<?php

namespace App\Services\AI;

use App\Exceptions\PlanLimitExceededException;
use App\Models\AiGenerationLog;
use App\Models\FillBlankAnswer;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Quiz;
use App\Models\User;
use App\Services\Quiz\PlanLimitService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use OpenAI\Laravel\Facades\OpenAI;

class QuizGeneratorService
{
    public function __construct(
        private QuizGeneratorPromptBuilder $promptBuilder,
        private QuizGeneratorResponseParser $parser,
    ) {}

    public function generate(User $user, Quiz $quiz, array $options): array
    {
        $log = AiGenerationLog::create([
            'user_id'             => $user->id,
            'quiz_id'             => $quiz->id,
            'prompt'              => $options['prompt'],
            'options'             => $options,
            'questions_generated' => 0,
            'tokens_used'         => 0,
            'model'               => 'gpt-4o',
            'status'              => 'success',
            'was_free'            => $user->ai_credits_free_remaining > 0,
        ]);

        try {
            $response = OpenAI::chat()->create([
                'model'    => 'gpt-4o',
                'messages' => [
                    ['role' => 'system', 'content' => $this->promptBuilder->buildSystemPrompt()],
                    ['role' => 'user',   'content' => $this->promptBuilder->buildUserPrompt($options)],
                ],
                'temperature' => 0.7,
            ]);

            $raw        = $response->choices[0]->message->content;
            $tokensUsed = $response->usage->totalTokens ?? 0;
            $questions  = array_slice($this->parser->parse($raw), 0, (int)($options['count'] ?? 50));

            $saved = $this->saveQuestions($quiz, $questions);

            $log->update([
                'questions_generated' => count($saved),
                'tokens_used'         => $tokensUsed,
                'status'              => 'success',
            ]);

            return $saved;
        } catch (\Throwable $e) {
            $log->update(['status' => 'failed', 'error_message' => $e->getMessage()]);
            Log::error('QuizGeneratorService error', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function stream(User $user, ?Quiz $quiz, array $options, callable $onChunk, ?string $collectionId = null): array
    {
        $log = AiGenerationLog::create([
            'user_id'             => $user->id,
            'quiz_id'             => $quiz?->id,
            'prompt'              => $options['prompt'],
            'options'             => $options,
            'questions_generated' => 0,
            'tokens_used'         => 0,
            'model'               => 'gpt-4o',
            'status'              => 'success',
            'was_free'            => $user->ai_credits_free_remaining > 0,
        ]);

        $buffer = '';

        try {
            $stream = OpenAI::chat()->createStreamed([
                'model'    => 'gpt-4o',
                'messages' => [
                    ['role' => 'system', 'content' => $this->promptBuilder->buildSystemPrompt()],
                    ['role' => 'user',   'content' => $this->promptBuilder->buildUserPrompt($options)],
                ],
                'temperature' => 0.7,
            ]);

            foreach ($stream as $response) {
                $delta   = $response->choices[0]->delta->content ?? '';
                $buffer .= $delta;
                $onChunk($delta);
            }

            $questions = array_slice($this->parser->parse($buffer), 0, (int)($options['count'] ?? 50));
            $saved     = $this->saveQuestions($user, $quiz, $questions, $collectionId);

            $log->update([
                'questions_generated' => count($saved),
                'status'              => 'success',
            ]);

            return $saved;
        } catch (\Throwable $e) {
            $log->update(['status' => 'failed', 'error_message' => $e->getMessage()]);
            throw $e;
        }
    }

    private function saveQuestions(User $user, ?Quiz $quiz, array $questions, ?string $collectionId = null): array
    {
        $saved     = [];
        $sortOrder = $quiz ? ($quiz->questions()->max('sort_order') ?? 0) : 0;
        $limitService = app(PlanLimitService::class);

        DB::transaction(function () use ($user, $quiz, $questions, $collectionId, $limitService, &$saved, &$sortOrder) {
            foreach ($questions as $q) {
                // Enforce per-quiz limit when saving to a quiz (not to the bank)
                if ($quiz) {
                    try {
                        $limitService->assertCanAddQuestion($user, $quiz->id);
                    } catch (PlanLimitExceededException $e) {
                        // Stop adding; already-saved questions in this loop are kept
                        break;
                    }
                }

                $question = Question::create([
                    'quiz_id'         => $quiz?->id,
                    'creator_id'      => $user->id,
                    'collection_id'   => $collectionId,
                    'type'            => $q['type'],
                    'content'         => $q['content'],
                    'explanation'     => $q['explanation'],
                    'marks'           => $q['marks'],
                    'negative_marks'  => $q['negative_marks'],
                    'sort_order'      => $quiz ? ++$sortOrder : 0,
                ]);

                foreach ($q['options'] as $i => $opt) {
                    QuestionOption::create([
                        'question_id' => $question->id,
                        'content'     => $opt['content'],
                        'is_correct'  => $opt['is_correct'],
                        'sort_order'  => $i + 1,
                    ]);
                }

                foreach ($q['blank_answers'] as $ans) {
                    FillBlankAnswer::create([
                        'question_id' => $question->id,
                        'answer'      => $ans,
                    ]);
                }

                $saved[] = $question->load('options', 'fillBlankAnswers');
            }
        });

        if ($quiz) {
            $quiz->update([
                'total_questions' => $quiz->questions()->count(),
                'total_marks'     => $quiz->questions()->sum('marks'),
            ]);
        }

        return $saved;
    }
}
