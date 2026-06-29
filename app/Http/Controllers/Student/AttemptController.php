<?php
namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Attempt;
use App\Models\AttemptAnswer;
use App\Models\Quiz;
use App\Models\QuizEnrollment;
use App\Services\Exam\AttemptService;
use App\Services\Exam\ScoringService;
use App\Services\Quiz\QuizAssignmentService;
use App\Services\Quiz\QuizPublishService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AttemptController extends Controller
{
    public function __construct(
        private AttemptService $attemptService,
        private ScoringService $scoringService,
        private QuizPublishService $publishService,
        private QuizAssignmentService $assignmentService,
    ) {}

    public function start(string $slug)
    {
        $quiz = Quiz::where('slug', $slug)->published()->firstOrFail();

        if (!$this->publishService->canAttempt($quiz)) {
            return back()->with('error', 'This quiz is not currently available for attempts.');
        }

        $user = auth()->user();

        if ($user->role === 'student' && ! $this->assignmentService->isAssignedToStudent($quiz, $user)) {
            return back()->with('error', 'This exam has not been assigned to you.');
        }

        $enrollment = QuizEnrollment::firstOrCreate(
            ['quiz_id' => $quiz->id, 'user_id' => $user->id],
            ['enrolled_at' => now(), 'source' => 'assigned']
        );

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

        return view('student.quiz.exam', compact('attempt'));
    }

    public function saveAnswer(Request $request, Attempt $attempt)
    {
        abort_unless($attempt->user_id === auth()->id(), 403);
        abort_unless($attempt->status === 'in_progress', 403);

        $attempt->loadMissing('quiz');

        $validated = $request->validate([
            'question_id'          => 'required|uuid',
            'selected_options'     => 'nullable|array',
            'selected_options.*'   => 'uuid',
            'text_answer'          => 'nullable|string|max:5000',
            'is_marked_for_review' => 'nullable|boolean',
        ]);

        $questionIds = $attempt->quiz->allQuestions()->pluck('id');
        abort_unless($questionIds->contains($validated['question_id']), 422);

        AttemptAnswer::updateOrCreate(
            ['attempt_id' => $attempt->id, 'question_id' => $validated['question_id']],
            [
                'selected_options'     => $validated['selected_options'] ?? [],
                'text_answer'          => $validated['text_answer'] ?? null,
                'is_marked_for_review' => (bool) ($validated['is_marked_for_review'] ?? false),
                'answered_at'          => now(),
            ]
        );

        return response()->json(['ok' => true]);
    }

    public function submit(Request $request, Attempt $attempt)
    {
        abort_unless($attempt->user_id === auth()->id(), 403);

        if ($attempt->status !== 'in_progress') {
            return response()->json(['redirect' => route('attempt.result', $attempt)]);
        }

        $elapsed = $request->validate(['elapsed_seconds' => 'nullable|integer|min:0'])['elapsed_seconds'] ?? null;

        $this->scoringService->evaluate($attempt, $elapsed);

        return response()->json(['redirect' => route('attempt.result', $attempt)]);
    }

    public function result(Attempt $attempt)
    {
        abort_unless($attempt->user_id === auth()->id(), 403);
        abort_unless(in_array($attempt->status, ['completed', 'timed_out', 'abandoned'], true), 404);

        $attempt->load(['quiz', 'answers.question.options', 'answers.question.fillBlankAnswers']);

        return view('student.quiz.result', compact('attempt'));
    }
}
