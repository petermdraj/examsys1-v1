<?php

namespace App\Services\Exam;

use App\Jobs\GenerateCertificateJob;
use App\Jobs\SendAttemptResultMailJob;
use App\Models\Quiz;

class QuizResultsPublishService
{
    public function publish(Quiz $quiz): int
    {
        abort_unless($quiz->hold_results_until_published, 422, 'This quiz does not hold results for publishing.');

        $quiz->update(['results_published_at' => now()]);

        $attempts = $quiz->attempts()
            ->whereIn('status', ['completed', 'terminated'])
            ->with(['user', 'quiz'])
            ->get();

        foreach ($attempts as $attempt) {
            SendAttemptResultMailJob::dispatch($attempt->user, $attempt);

            if ($attempt->is_passed && $attempt->quiz->certificate_enabled) {
                GenerateCertificateJob::dispatch($attempt);
            }
        }

        return $attempts->count();
    }
}
