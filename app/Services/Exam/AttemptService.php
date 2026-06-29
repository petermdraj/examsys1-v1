<?php
namespace App\Services\Exam;

use App\Models\Attempt;
use App\Models\QuizEnrollment;

class AttemptService
{
    public function startAttempt(QuizEnrollment $enrollment): Attempt
    {
        $attemptNumber = $enrollment->attempts()->count() + 1;
        return Attempt::create([
            'quiz_id' => $enrollment->quiz_id,
            'user_id' => $enrollment->user_id,
            'enrollment_id' => $enrollment->id,
            'attempt_number' => $attemptNumber,
            'status' => 'in_progress',
            'started_at' => now(),
            'last_activity_at' => now(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
