<?php

namespace Tests\Feature;

use App\Models\Attempt;
use App\Models\Quiz;
use App\Models\QuizEnrollment;
use App\Models\User;
use App\Services\Lecturer\LecturerDashboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LecturerDashboardServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_quiz_status_breakdown_scopes_to_lecturer(): void
    {
        $lecturer = User::factory()->create(['role' => 'lecturer']);
        $other = User::factory()->create(['role' => 'lecturer']);

        Quiz::factory()->create(['lecturer_id' => $lecturer->id, 'status' => 'published']);
        Quiz::factory()->create(['lecturer_id' => $lecturer->id, 'status' => 'draft']);
        Quiz::factory()->create(['lecturer_id' => $other->id, 'status' => 'published']);

        $breakdown = app(LecturerDashboardService::class)
            ->forUser($lecturer)
            ->getQuizStatusBreakdown();

        $this->assertEquals(1, $breakdown['published']);
        $this->assertEquals(1, $breakdown['draft']);
        $this->assertEquals(2, $breakdown['total']);
    }

    public function test_exam_performance_returns_per_quiz_pass_rate_and_duration(): void
    {
        $lecturer = User::factory()->create(['role' => 'lecturer']);
        $student  = User::factory()->create(['role' => 'student']);
        $quiz     = Quiz::factory()->create(['lecturer_id' => $lecturer->id, 'total_attempts' => 2]);

        $enrollment = QuizEnrollment::factory()->create([
            'quiz_id' => $quiz->id,
            'user_id' => $student->id,
        ]);

        Attempt::factory()->create([
            'quiz_id'             => $quiz->id,
            'user_id'             => $student->id,
            'enrollment_id'       => $enrollment->id,
            'status'              => 'completed',
            'is_passed'           => true,
            'time_taken_seconds'  => 600,
        ]);
        Attempt::factory()->create([
            'quiz_id'             => $quiz->id,
            'user_id'             => $student->id,
            'enrollment_id'       => $enrollment->id,
            'status'              => 'completed',
            'is_passed'           => false,
            'time_taken_seconds'  => 1200,
            'attempt_number'      => 2,
        ]);

        $rows = app(LecturerDashboardService::class)
            ->forUser($lecturer)
            ->getExamPerformance();

        $this->assertCount(1, $rows);
        $this->assertEquals(50.0, $rows->first()['pass_rate']);
        $this->assertEquals(15.0, $rows->first()['avg_duration_min']);
        $this->assertEquals(2, $rows->first()['attempts']);
    }
}
