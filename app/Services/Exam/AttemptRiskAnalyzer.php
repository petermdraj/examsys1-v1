<?php

namespace App\Services\Exam;

use App\Models\Attempt;
use Carbon\Carbon;

class AttemptRiskAnalyzer
{
    public function analyze(Attempt $attempt, ?string $event = null): Attempt
    {
        $score = $this->calculateScore($attempt);
        $riskLevel = $this->determineRiskLevel($score);

        $events = $attempt->flagged_events ?? [];
        if ($event) {
            $events[] = '[' . now()->format('H:i:s') . '] ' . $event;
        }

        $attempt->update([
            'risk_score'       => $score,
            'risk_level'       => $riskLevel,
            'flagged_events'   => $events,
            'last_activity_at' => now(),
        ]);

        return $attempt->fresh();
    }

    public function recordViolation(Attempt $attempt, string $type, string $message): Attempt
    {
        $attempt->increment('tab_switch_count');

        return $this->analyze($attempt->fresh(), $message);
    }

    protected function calculateScore(Attempt $attempt): int
    {
        $switches = (int) $attempt->tab_switch_count;
        $events = $attempt->flagged_events ?? [];

        $score = 0;

        if ($switches > 5) {
            $score = 85;
        } elseif ($switches > 2) {
            $score = 45;
        } elseif ($switches > 0) {
            $score = 20;
        }

        if (count($events) > 10) {
            $score = min(100, $score + 15);
        }

        return min(100, $score);
    }

    protected function determineRiskLevel(int $score): string
    {
        if ($score >= 71) {
            return 'critical';
        }

        if ($score >= 31) {
            return 'warning';
        }

        return 'low';
    }
}
