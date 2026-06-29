<?php

namespace App\Services\AI;

use App\Models\AiGenerationLog;
use App\Models\FillBlankAnswer;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Quiz;
use App\Models\User;
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
            'was_free'            => true,
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
            $questions  = $this->parser->parse($raw);
            if (! empty($options['count'])) {
                $questions = array_slice($questions, 0, (int) $options['count']);
            }

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
            'was_free'            => true,
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

            $questions = $this->parser->parse($buffer);
            if (! empty($options['count'])) {
                $questions = array_slice($questions, 0, (int) $options['count']);
            }
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

        DB::transaction(function () use ($user, $quiz, $questions, $collectionId, &$saved, &$sortOrder) {
            foreach ($questions as $q) {
                $question = Question::create([
                    'quiz_id'         => $quiz?->id,
                    'lecturer_id'      => $user->id,
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
