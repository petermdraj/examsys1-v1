<?php
namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Attempt;
use App\Models\AttemptAnswer;
use App\Models\Quiz;
use App\Models\QuizEnrollment;
use App\Services\Exam\AttemptService;
use App\Services\Exam\ScoringService;
use App\Services\Quiz\QuizPublishService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AttemptController extends Controller
{
    public function __construct(
        private AttemptService $attemptService,
        private ScoringService $scoringService,
        private QuizPublishService $publishService,
    ) {}

    public function start(string $slug)
    {
        $quiz = Quiz::where('slug', $slug)->published()->firstOrFail();

        if (!$this->publishService->canAttempt($quiz)) {
            return back()->with('error', 'This quiz is not currently available for attempts.');
        }

        $user = auth()->user();

        // For paid quizzes, must be enrolled
        if (!$quiz->isFree()) {
            $enrollment = QuizEnrollment::where('quiz_id', $quiz->id)
                ->where('user_id', $user->id)->first();

            if (!$enrollment) {
                return redirect()->route('quiz.checkout', $quiz->slug)
                    ->with('error', 'Please purchase this quiz first.');
            }
        } else {
            $enrollment = QuizEnrollment::firstOrCreate(
                ['quiz_id' => $quiz->id, 'user_id' => $user->id],
                ['enrolled_at' => now(), 'source' => 'free']
            );
        }

        // Check max_attempts
        if ($quiz->max_attempts) {
            $doneCount = Attempt::where('quiz_id', $quiz->id)
                ->where('user_id', $user->id)
                ->where('status', 'completed')
                ->count();
            if ($doneCount >= $quiz->max_attempts) {
                return back()->with('error', "You have used all {$quiz->max_attempts} allowed attempts.");
            }
        }

        // Abandon any in-progress attempt
        Attempt::where('quiz_id', $quiz->id)
            ->where('user_id', $user->id)
            ->where('status', 'in_progress')
            ->update(['status' => 'abandoned']);

        $attempt = $this->attemptService->startAttempt($enrollment);
        return redirect()->route('attempt.show', $attempt);
    }

    public function show(Attempt $attempt)
    {
        abort_unless($attempt->user_id === auth()->id(), 403);
        abort_unless($attempt->status === 'in_progress', 404);

        return view('customer.quiz.exam', compact('attempt'));
    }

    public function saveAnswer(Request $request, Attempt $attempt)
    {
        abort_unless($attempt->user_id === auth()->id(), 403);
        abort_unless($attempt->status === 'in_progress', 403);
        AttemptAnswer::updateOrCreate(
            ['attempt_id' => $attempt->id, 'question_id' => $request->question_id],
            [
                'selected_options' => $request->selected_options ?? [],
                'text_answer' => $request->text_answer,
                'is_marked_for_review' => $request->boolean('is_marked_for_review'),
                'answered_at' => now(),
            ]
        );
        return response()->json(['ok' => true]);
    }

    public function submit(Attempt $attempt)
    {
        abort_unless($attempt->user_id === auth()->id(), 403);

        $elapsedSeconds = request()->integer('elapsed_seconds') ?: null;

        DB::transaction(function () use ($attempt, $elapsedSeconds) {
            // Lock the row so concurrent submit requests queue up rather than both scoring
            $fresh = Attempt::lockForUpdate()->find($attempt->id);

            if ($fresh->status === 'completed') {
                return;
            }

            $this->scoringService->evaluate($fresh, $elapsedSeconds);
        });

        return response()->json(['redirect' => route('attempt.result', $attempt)]);
    }

    public function result(Attempt $attempt)
    {
        abort_unless($attempt->user_id === auth()->id(), 403);
        abort_unless($attempt->status === 'completed', 404);
        $attempt->load(['quiz', 'answers.question.options']);
        return view('customer.quiz.result', compact('attempt'));
    }
}
