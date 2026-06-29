<?php

namespace Tests\Feature;

use App\Models\Attempt;
use App\Models\Quiz;
use App\Models\QuizEnrollment;
use App\Models\User;
use App\Services\Exam\AttemptMonitoringService;
use App\Services\Exam\AttemptRiskAnalyzer;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttemptProctoringTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
    }

    private function makeAttempt(bool $proctoring = true): array
    {
        $user = User::factory()->create(['role' => 'student']);
        $quiz = Quiz::factory()->create([
            'status'             => 'published',
            'proctoring_enabled' => $proctoring,
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

    public function test_violation_increments_tab_switch_count_when_proctoring_enabled(): void
    {
        ['user' => $user, 'attempt' => $attempt] = $this->makeAttempt(true);

        $this->actingAs($user)
            ->postJson(route('attempt.violation', $attempt), [
                'type'    => 'tab_switch',
                'message' => 'User switched browser tab',
            ])
            ->assertOk();

        $attempt->refresh();
        $this->assertEquals(1, $attempt->tab_switch_count);
        $this->assertNotEmpty($attempt->flagged_events);
        $this->assertGreaterThan(0, $attempt->risk_score);
    }

    public function test_violation_rejected_when_proctoring_disabled(): void
    {
        ['user' => $user, 'attempt' => $attempt] = $this->makeAttempt(false);

        $this->actingAs($user)
            ->postJson(route('attempt.violation', $attempt), ['type' => 'tab_switch'])
            ->assertForbidden();

        $this->assertEquals(0, $attempt->fresh()->tab_switch_count);
    }

    public function test_violation_rejected_when_attempt_paused(): void
    {
        ['user' => $user, 'attempt' => $attempt] = $this->makeAttempt(true);
        $attempt->update(['status' => 'paused']);

        $this->actingAs($user)
            ->postJson(route('attempt.violation', $attempt), ['type' => 'tab_switch'])
            ->assertForbidden();
    }
}
