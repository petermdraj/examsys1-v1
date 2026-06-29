<?php

namespace App\Services\Exam;

use App\Models\Attempt;

class AttemptInterventionService
{
    public function __construct(private ScoringService $scoringService) {}

    public function pause(Attempt $attempt): Attempt
    {
        abort_unless($attempt->status === 'in_progress', 422, 'Only in-progress attempts can be paused.');

        $attempt->update(['status' => 'paused']);

        return $attempt->fresh();
    }

    public function resume(Attempt $attempt): Attempt
    {
        abort_unless($attempt->status === 'paused', 422, 'Only paused attempts can be resumed.');

        $attempt->update([
            'status'           => 'in_progress',
            'last_activity_at' => now(),
        ]);

        return $attempt->fresh();
    }

    public function terminate(Attempt $attempt): Attempt
    {
        abort_unless($attempt->isActive(), 422, 'Only active attempts can be force-submitted.');

        return $this->scoringService->evaluate($attempt, null, 'terminated');
    }

    public function reopen(Attempt $attempt): Attempt
    {
        abort_unless(in_array($attempt->status, ['terminated', 'completed'], true), 422, 'Only finished attempts can be reopened.');

        $attempt->update([
            'status'             => 'in_progress',
            'submitted_at'       => null,
            'score'              => null,
            'total_marks'        => null,
            'percentage'         => null,
            'is_passed'          => null,
            'time_taken_seconds'   => null,
            'last_activity_at'   => now(),
        ]);

        return $attempt->fresh();
    }
}
