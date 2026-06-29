<?php

namespace Tests\Feature;

use App\Models\Attempt;
use App\Models\Quiz;
use App\Models\QuizEnrollment;
use App\Models\User;
use App\Services\Exam\AttemptMonitoringService;
use App\Services\Exam\AttemptRiskAnalyzer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttemptMonitoringServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_live_kpis_count_active_and_paused(): void
    {
        $quiz = Quiz::factory()->create(['status' => 'published']);
        $user = User::factory()->create(['role' => 'student']);
        $enrollment = QuizEnrollment::factory()->create(['quiz_id' => $quiz->id, 'user_id' => $user->id]);

        Attempt::factory()->create([
            'quiz_id' => $quiz->id, 'user_id' => $user->id,
            'enrollment_id' => $enrollment->id, 'status' => 'in_progress',
        ]);
        Attempt::factory()->create([
            'quiz_id' => $quiz->id, 'user_id' => $user->id,
            'enrollment_id' => $enrollment->id, 'status' => 'paused',
        ]);

        $kpis = app(AttemptMonitoringService::class)->getLiveKpis();

        $this->assertEquals(1, $kpis['active_users']);
        $this->assertEquals(1, $kpis['paused']);
    }

    public function test_monthly_chart_returns_twelve_months(): void
    {
        $data = app(AttemptMonitoringService::class)->getMonthlyChartData();

        $this->assertCount(12, $data);
        $this->assertArrayHasKey('label', $data[0]);
        $this->assertArrayHasKey('attendance_val', $data[0]);
        $this->assertArrayHasKey('pass_val', $data[0]);
        $this->assertArrayHasKey('attendance_height', $data[0]);
        $this->assertArrayHasKey('pass_height', $data[0]);
    }

    public function test_exam_status_breakdown_counts_quizzes_by_status(): void
    {
        Quiz::factory()->create(['status' => 'published']);
        Quiz::factory()->create(['status' => 'published']);
        Quiz::factory()->create(['status' => 'scheduled']);
        Quiz::factory()->create(['status' => 'draft']);

        $breakdown = app(AttemptMonitoringService::class)->getExamStatusBreakdown();

        $this->assertEquals(2, $breakdown['published']);
        $this->assertEquals(1, $breakdown['scheduled']);
        $this->assertEquals(1, $breakdown['draft']);
        $this->assertEquals(4, $breakdown['total']);
        $this->assertEquals(50, $breakdown['published_pct']);
        $this->assertArrayHasKey('gradient', $breakdown);
    }

    public function test_risk_alerts_returns_flagged_sessions(): void
    {
        $quiz = Quiz::factory()->create(['status' => 'published']);
        $user = User::factory()->create(['role' => 'student']);
        $enrollment = QuizEnrollment::factory()->create(['quiz_id' => $quiz->id, 'user_id' => $user->id]);

        $attempt = Attempt::factory()->create([
            'quiz_id' => $quiz->id, 'user_id' => $user->id,
            'enrollment_id' => $enrollment->id, 'status' => 'in_progress',
            'tab_switch_count' => 6,
        ]);

        app(AttemptRiskAnalyzer::class)->analyze($attempt);

        $alerts = app(AttemptMonitoringService::class)->getRiskAlerts();

        $this->assertGreaterThanOrEqual(1, $alerts->count());
        $this->assertEquals('critical', $alerts->first()->level);
    }
}
