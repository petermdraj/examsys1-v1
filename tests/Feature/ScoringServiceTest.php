<?php

namespace Tests\Feature;

use App\Models\Attempt;
use App\Models\AttemptAnswer;
use App\Models\FillBlankAnswer;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Quiz;
use App\Models\QuizEnrollment;
use App\Models\User;
use App\Services\Exam\ScoringService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScoringServiceTest extends TestCase
{
    use RefreshDatabase;

    private ScoringService $scorer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->scorer = app(ScoringService::class);
    }

    private function makeAttempt(array $quizAttrs = []): Attempt
    {
        $user = User::factory()->create();
        $quiz = Quiz::factory()->create(array_merge([
            'pass_percentage'          => 60,
            'negative_marking_enabled' => false,
            'certificate_enabled'      => false,
        ], $quizAttrs));
        $enrollment = QuizEnrollment::factory()->create(['quiz_id' => $quiz->id, 'user_id' => $user->id]);
        return Attempt::factory()->create([
            'quiz_id'       => $quiz->id,
            'user_id'       => $user->id,
            'enrollment_id' => $enrollment->id,
            'status'        => 'in_progress',
            'started_at'    => now()->subMinutes(5),
        ]);
    }

    public function test_correct_mcq_awards_marks(): void
    {
        $attempt = $this->makeAttempt();
        $question = Question::factory()->create(['quiz_id' => $attempt->quiz_id, 'type' => 'mcq_single', 'marks' => 2, 'negative_marks' => 0]);
        $correct = QuestionOption::factory()->create(['question_id' => $question->id, 'is_correct' => true]);
        QuestionOption::factory()->create(['question_id' => $question->id, 'is_correct' => false]);

        AttemptAnswer::factory()->create([
            'attempt_id'       => $attempt->id,
            'question_id'      => $question->id,
            'selected_options' => [$correct->id],
        ]);

        $result = $this->scorer->evaluate($attempt);

        $this->assertEquals(2.0, $result->score);
        $this->assertEquals('completed', $result->status);
    }

    public function test_wrong_mcq_with_negative_marking_deducts(): void
    {
        $attempt = $this->makeAttempt(['negative_marking_enabled' => true]);
        $question = Question::factory()->create(['quiz_id' => $attempt->quiz_id, 'type' => 'mcq_single', 'marks' => 1, 'negative_marks' => 0.25]);
        QuestionOption::factory()->create(['question_id' => $question->id, 'is_correct' => true]);
        $wrong = QuestionOption::factory()->create(['question_id' => $question->id, 'is_correct' => false]);

        AttemptAnswer::factory()->create([
            'attempt_id'       => $attempt->id,
            'question_id'      => $question->id,
            'selected_options' => [$wrong->id],
        ]);

        $result = $this->scorer->evaluate($attempt);

        // score clamped to 0 minimum
        $this->assertEquals(0.0, $result->score);

        // but marks_earned stored as -0.25
        $this->assertEquals(-0.25, $attempt->answers()->first()->marks_earned);
    }

    public function test_null_question_is_skipped_gracefully(): void
    {
        $attempt = $this->makeAttempt();

        // Create a real question, attach an answer, then delete the question
        // to simulate a question being deleted after an attempt started
        $question = Question::factory()->create(['quiz_id' => $attempt->quiz_id, 'type' => 'mcq_single', 'marks' => 1]);
        $opt = QuestionOption::factory()->create(['question_id' => $question->id, 'is_correct' => true]);

        AttemptAnswer::factory()->create([
            'attempt_id'       => $attempt->id,
            'question_id'      => $question->id,
            'selected_options' => [$opt->id],
        ]);

        // Delete the question — FK on attempt_answers is CASCADE, so we must force-delete via DB
        \DB::statement('PRAGMA foreign_keys = OFF');
        $question->delete();
        \DB::statement('PRAGMA foreign_keys = ON');

        // Should not throw, orphan answer has null question and is skipped
        $result = $this->scorer->evaluate($attempt);
        $this->assertEquals('completed', $result->status);
        $this->assertEquals(0.0, $result->score);
    }

    public function test_fill_blank_exact_match(): void
    {
        $attempt = $this->makeAttempt();
        $question = Question::factory()->create(['quiz_id' => $attempt->quiz_id, 'type' => 'fill_blank', 'marks' => 1, 'negative_marks' => 0]);
        FillBlankAnswer::factory()->create(['question_id' => $question->id, 'answer' => 'Laravel', 'is_regex' => false]);

        AttemptAnswer::factory()->create([
            'attempt_id'  => $attempt->id,
            'question_id' => $question->id,
            'text_answer' => 'laravel', // case insensitive
        ]);

        $result = $this->scorer->evaluate($attempt);
        $this->assertEquals(1.0, $result->score);
    }

    public function test_fill_blank_regex_match(): void
    {
        $attempt = $this->makeAttempt();
        $question = Question::factory()->create(['quiz_id' => $attempt->quiz_id, 'type' => 'fill_blank', 'marks' => 1, 'negative_marks' => 0]);
        FillBlankAnswer::factory()->create(['question_id' => $question->id, 'answer' => '^php\s*\d+$', 'is_regex' => true]);

        AttemptAnswer::factory()->create([
            'attempt_id'  => $attempt->id,
            'question_id' => $question->id,
            'text_answer' => 'PHP 8',
        ]);

        $result = $this->scorer->evaluate($attempt);
        $this->assertEquals(1.0, $result->score);
    }

    public function test_short_answer_exact_match(): void
    {
        $attempt = $this->makeAttempt();
        $question = Question::factory()->create(['quiz_id' => $attempt->quiz_id, 'type' => 'short_answer', 'marks' => 2, 'negative_marks' => 0]);
        FillBlankAnswer::factory()->create(['question_id' => $question->id, 'answer' => 'Paris', 'is_regex' => false]);

        AttemptAnswer::factory()->create([
            'attempt_id'  => $attempt->id,
            'question_id' => $question->id,
            'text_answer' => 'paris',
        ]);

        $result = $this->scorer->evaluate($attempt);
        $this->assertEquals(2.0, $result->score);
    }

    public function test_is_passed_based_on_pass_percentage(): void
    {
        $attempt = $this->makeAttempt(['pass_percentage' => 60, 'total_marks' => 1]);
        $question = Question::factory()->create(['quiz_id' => $attempt->quiz_id, 'type' => 'mcq_single', 'marks' => 1]);
        $correct = QuestionOption::factory()->create(['question_id' => $question->id, 'is_correct' => true]);
        AttemptAnswer::factory()->create(['attempt_id' => $attempt->id, 'question_id' => $question->id, 'selected_options' => [$correct->id]]);

        $result = $this->scorer->evaluate($attempt);
        $this->assertTrue($result->is_passed);
    }

    /**
     * Regression: visiting 2 of 10 questions and answering 1 correctly must yield 10%, not 50%.
     * ScoringService must use quiz.total_marks as denominator, not the count of visited answers.
     */
    public function test_percentage_uses_total_quiz_marks_not_visited_answers(): void
    {
        $attempt = $this->makeAttempt(['pass_percentage' => 60]);
        $quiz    = $attempt->quiz;

        // Create 10 questions, update the quiz's denormalized total_marks to 10
        $questions = Question::factory()->count(10)->create([
            'quiz_id' => $quiz->id, 'type' => 'mcq_single', 'marks' => 1, 'negative_marks' => 0,
        ]);
        $quiz->update(['total_marks' => 10, 'total_questions' => 10]);

        // User visits only the first two questions; answers the first correctly
        $correct = QuestionOption::factory()->create(['question_id' => $questions[0]->id, 'is_correct' => true]);
        QuestionOption::factory()->create(['question_id' => $questions[0]->id, 'is_correct' => false]);
        AttemptAnswer::factory()->create([
            'attempt_id'  => $attempt->id,
            'question_id' => $questions[0]->id,
            'selected_options' => [$correct->id],
        ]);

        // Second question visited but not answered (no answer record created — mirrors real behaviour)

        $result = $this->scorer->evaluate($attempt);

        // 1 correct out of 10 total = 10%, not 50%
        $this->assertEquals(1.0, $result->score);
        $this->assertEquals(10.0, $result->percentage);
        $this->assertFalse($result->is_passed);
    }
}
