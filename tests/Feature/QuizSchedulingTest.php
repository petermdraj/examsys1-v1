<?php

namespace Tests\Feature;

use App\Models\Attempt;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\QuizEnrollment;
use App\Models\User;
use App\Services\Quiz\QuizAssignmentService;
use App\Services\Quiz\QuizPublishService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuizSchedulingTest extends TestCase
{
    use RefreshDatabase;

    public function test_scheduled_quiz_is_published_when_start_time_arrives(): void
    {
        $lecturer = User::factory()->create(['role' => 'lecturer']);
        $quiz = Quiz::factory()->create([
            'lecturer_id'     => $lecturer->id,
            'status'          => 'scheduled',
            'start_at'        => now()->subMinute(),
            'total_questions' => 1,
        ]);
        Question::factory()->create(['quiz_id' => $quiz->id, 'lecturer_id' => $lecturer->id]);

        $count = app(QuizPublishService::class)->publishDueScheduled();

        $this->assertEquals(1, $count);
        $this->assertEquals('published', $quiz->fresh()->status);
    }

    public function test_can_attempt_respects_start_and_end_window(): void
    {
        $service = app(QuizPublishService::class);

        $beforeStart = Quiz::factory()->create([
            'status'   => 'published',
            'start_at' => now()->addHour(),
        ]);
        $this->assertEquals('before_start', $service->attemptBlockReason($beforeStart));

        $afterEnd = Quiz::factory()->create([
            'status' => 'published',
            'end_at' => now()->subMinute(),
        ]);
        $this->assertEquals('after_end', $service->attemptBlockReason($afterEnd));

        $open = Quiz::factory()->create([
            'status'   => 'published',
            'start_at' => now()->subHour(),
            'end_at'   => now()->addHour(),
        ]);
        $this->assertTrue($service->canAttempt($open));
    }

    public function test_effective_duration_is_capped_by_window_end(): void
    {
        $service = app(QuizPublishService::class);

        $quiz = Quiz::factory()->create([
            'duration_minutes' => 90,
            'end_at'           => now()->addMinutes(30),
        ]);

        $this->assertEquals(30 * 60, $service->effectiveDurationSeconds($quiz));
    }

    public function test_close_exam_windows_force_submits_active_attempts(): void
    {
        $lecturer = User::factory()->create(['role' => 'lecturer']);
        $student  = User::factory()->create(['role' => 'student']);
        $quiz = Quiz::factory()->create([
            'lecturer_id'         => $lecturer->id,
            'status'              => 'published',
            'force_submit_at_end' => true,
            'end_at'              => now()->subMinute(),
            'total_questions'     => 1,
        ]);
        Question::factory()->create(['quiz_id' => $quiz->id, 'lecturer_id' => $lecturer->id]);

        $enrollment = QuizEnrollment::factory()->create([
            'quiz_id' => $quiz->id,
            'user_id' => $student->id,
        ]);

        $attempt = Attempt::factory()->create([
            'quiz_id'       => $quiz->id,
            'user_id'       => $student->id,
            'enrollment_id' => $enrollment->id,
            'status'        => 'in_progress',
        ]);

        $this->artisan('examsys:close-exam-windows')->assertSuccessful();

        $this->assertEquals('terminated', $attempt->fresh()->status);
    }

    public function test_student_sees_blocked_message_before_exam_opens(): void
    {
        $lecturer = User::factory()->create(['role' => 'lecturer']);
        $batch    = \App\Models\StudentBatch::create(['name' => 'B1', 'code' => 'B1', 'is_active' => true]);
        $student  = User::factory()->create([
            'role'             => 'student',
            'student_batch_id' => $batch->id,
        ]);

        $quiz = Quiz::factory()->create([
            'lecturer_id' => $lecturer->id,
            'status'      => 'published',
            'visibility'  => 'private',
            'start_at'    => now()->addDay(),
            'total_questions' => 1,
        ]);
        Question::factory()->create(['quiz_id' => $quiz->id, 'lecturer_id' => $lecturer->id]);

        app(QuizAssignmentService::class)->assignToBatch($quiz, $lecturer, $batch);

        $this->actingAs($student)
            ->get(route('quizzes.show', $quiz->slug))
            ->assertOk()
            ->assertSee(__('quiz.attempt_blocked_before_start'), false);
    }

    public function test_attempt_start_is_rejected_before_window_opens(): void
    {
        $lecturer = User::factory()->create(['role' => 'lecturer']);
        $student  = User::factory()->create(['role' => 'student']);

        $quiz = Quiz::factory()->create([
            'lecturer_id' => $lecturer->id,
            'status'      => 'published',
            'start_at'    => now()->addHour(),
            'total_questions' => 1,
        ]);
        Question::factory()->create(['quiz_id' => $quiz->id, 'lecturer_id' => $lecturer->id]);

        QuizEnrollment::factory()->create(['quiz_id' => $quiz->id, 'user_id' => $student->id]);

        $this->actingAs($student)
            ->post(route('attempt.start', $quiz->slug))
            ->assertRedirect()
            ->assertSessionHas('error');
    }
}
