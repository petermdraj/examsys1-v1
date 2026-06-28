<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\InsufficientAiCreditsException;
use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Services\AI\AiCreditService;
use App\Services\AI\QuizGeneratorService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AiGenerateController extends Controller
{
    public function __construct(
        private AiCreditService $creditService,
        private QuizGeneratorService $generator,
    ) {}

    public function stream(Request $request): StreamedResponse
    {
        $saveToBank = (bool) $request->input('save_to_bank', false);

        $request->validate([
            'quiz_id'            => $saveToBank ? 'nullable|uuid|exists:quizzes,id' : 'required|uuid|exists:quizzes,id',
            'collection_id'      => 'nullable|uuid|exists:question_collections,id',
            'prompt'             => 'required|string|min:3|max:500',
            'count'              => 'integer|min:1|max:50',
            'type'               => 'in:mcq_single,mcq_multiple,fill_blank,true_false,mixed',
            'difficulty'         => 'in:easy,medium,hard,mixed',
            'language'           => 'string|max:10',
            'options_count'      => 'integer|min:2|max:6',
            'marks_per_question' => 'numeric|min:0.5|max:100',
            'negative_marking'   => 'boolean',
        ]);

        $user = $request->user();
        $quiz = null;
        if (!$saveToBank && $request->quiz_id) {
            $quiz = Quiz::where('id', $request->quiz_id)
                ->where('creator_id', $user->id)
                ->firstOrFail();
        }

        $options = $request->only([
            'prompt', 'count', 'type', 'difficulty',
            'language', 'options_count', 'marks_per_question', 'negative_marking',
        ]);
        $options['count'] = $options['count'] ?? 10;

        try {
            $deductedFrom = $this->creditService->consume($user);
        } catch (InsufficientAiCreditsException $e) {
            return response()->json(['error' => $e->getMessage()], 402);
        }

        $collectionId = $saveToBank ? $request->input('collection_id') : null;

        return response()->stream(function () use ($user, $quiz, $options, $deductedFrom, $collectionId, $saveToBank) {
            $questions = [];

            try {
                $questions = $this->generator->stream($user, $quiz, $options, function (string $chunk) {
                    echo 'data: ' . json_encode(['chunk' => $chunk]) . "\n\n";
                    ob_flush();
                    flush();
                }, $collectionId);
            } catch (\Throwable $e) {
                $this->creditService->refund($user, $deductedFrom);
                echo 'data: ' . json_encode(['error' => $e->getMessage()]) . "\n\n";
                ob_flush();
                flush();
                return;
            }

            echo 'data: ' . json_encode([
                'done'            => true,
                'question_count'  => count($questions),
                'questions'       => collect($questions)->map(fn($q) => [
                    'id'           => $q->id,
                    'type'         => $q->type,
                    'content'      => $q->content,
                    'explanation'  => $q->explanation,
                    'marks'        => (float) $q->marks,
                    'negative_marks' => (float) $q->negative_marks,
                    'options'      => $q->options->map(fn($o) => [
                        'content'    => $o->content,
                        'is_correct' => (bool) $o->is_correct,
                    ])->toArray(),
                    'blank_answers' => $q->fillBlankAnswers->pluck('answer')->toArray(),
                ])->toArray(),
            ]) . "\n\n";
            ob_flush();
            flush();
        }, 200, [
            'Content-Type'      => 'text/event-stream',
            'Cache-Control'     => 'no-cache',
            'X-Accel-Buffering' => 'no',
            'Connection'        => 'keep-alive',
        ]);
    }
}
