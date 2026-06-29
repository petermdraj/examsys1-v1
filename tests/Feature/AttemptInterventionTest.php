<?php

namespace Tests\Feature;

use App\Models\Attempt;
use App\Models\AttemptAnswer;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Quiz;
use App\Models\QuizEnrollment;
use App\Models\User;
use App\Services\Exam\AttemptInterventionService;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttemptInterventionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
    }

    private function makeAttempt(): array
    {
        $user = User::factory()->create(['role' => 'student']);
        $quiz = Quiz::factory()->create([
            'status'        => 'published',
            'total_marks'   => 2,
            'total_questions' => 1,
            'pass_percentage' => 60,
        ]);
        $enrollment = QuizEnrollment::factory()->create([
            'quiz_id' => $quiz->id,
            'user_id' => $user->id,
        ]);
        $attempt = Attempt::factory()->create([
            'quiz_id'       => $quiz->id,
            'user_id'       => $user->id,
            'enrollment_id' => $enrollment->id,
            'status'        => 'in_progress',
        ]);

        return compact('user', 'quiz', 'attempt');
    }

    public function test_pause_and_resume_attempt(): void
    {
        ['attempt' => $attempt] = $this->makeAttempt();
        $service = app(AttemptInterventionService::class);

        $service->pause($attempt);
        $this->assertEquals('paused', $attempt->fresh()->status);

        $service->resume($attempt);
        $this->assertEquals('in_progress', $attempt->fresh()->status);
    }

    public function test_student_cannot_save_answer_while_paused(): void
    {
        ['user' => $user, 'quiz' => $quiz, 'attempt' => $attempt] = $this->makeAttempt();
        $question = Question::factory()->create(['quiz_id' => $quiz->id, 'type' => 'mcq_single']);
        $attempt->update(['status' => 'paused']);

        $this->actingAs($user)
            ->postJson(route('attempt.answer', $attempt), [
                'question_id'      => $question->id,
                'selected_options' => [],
            ])
            ->assertForbidden();
    }

    public function test_terminate_force_submits_attempt(): void
    {
        ['quiz' => $quiz, 'attempt' => $attempt] = $this->makeAttempt();
        $question = Question::factory()->create([
            'quiz_id' => $quiz->id,
            'type'    => 'mcq_single',
            'marks'   => 2,
        ]);
        $correct = QuestionOption::factory()->create([
            'question_id' => $question->id,
            'is_correct'  => true,
        ]);
        AttemptAnswer::factory()->create([
            'attempt_id'       => $attempt->id,
            'question_id'      => $question->id,
            'selected_options' => [$correct->id],
        ]);

        app(AttemptInterventionService::class)->terminate($attempt->fresh());

        $attempt->refresh();
        $this->assertEquals('terminated', $attempt->status);
        $this->assertNotNull($attempt->submitted_at);
        $this->assertEquals(2.0, (float) $attempt->score);
    }
}
