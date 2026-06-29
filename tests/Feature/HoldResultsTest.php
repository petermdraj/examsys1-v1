<?php

namespace Tests\Feature;

use App\Models\Attempt;
use App\Models\Quiz;
use App\Models\QuizEnrollment;
use App\Models\User;
use App\Services\Exam\QuizResultsPublishService;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class HoldResultsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
    }

    public function test_student_sees_pending_page_when_results_held(): void
    {
        $user = User::factory()->create(['role' => 'student']);
        $quiz = Quiz::factory()->create([
            'hold_results_until_published' => true,
            'results_published_at'         => null,
        ]);
        $enrollment = QuizEnrollment::factory()->create(['quiz_id' => $quiz->id, 'user_id' => $user->id]);
        $attempt = Attempt::factory()->create([
            'quiz_id'       => $quiz->id,
            'user_id'       => $user->id,
            'enrollment_id' => $enrollment->id,
            'status'        => 'completed',
            'submitted_at'  => now(),
            'percentage'    => 80,
            'is_passed'     => true,
        ]);

        $this->actingAs($user)
            ->get(route('attempt.result', $attempt))
            ->assertOk()
            ->assertSee(__('exam.result_pending_title'));
    }

    public function test_publish_makes_results_visible(): void
    {
        $user = User::factory()->create(['role' => 'student']);
        $quiz = Quiz::factory()->create([
            'hold_results_until_published' => true,
            'results_published_at'         => null,
        ]);
        $enrollment = QuizEnrollment::factory()->create(['quiz_id' => $quiz->id, 'user_id' => $user->id]);
        $attempt = Attempt::factory()->create([
            'quiz_id'       => $quiz->id,
            'user_id'       => $user->id,
            'enrollment_id' => $enrollment->id,
            'status'        => 'completed',
            'submitted_at'  => now(),
            'percentage'    => 80,
            'is_passed'     => true,
        ]);

        Queue::fake();

        app(QuizResultsPublishService::class)->publish($quiz);

        $this->actingAs($user)
            ->get(route('attempt.result', $attempt))
            ->assertOk()
            ->assertSee(__('exam.your_result'));
    }
}
