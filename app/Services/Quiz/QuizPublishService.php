<?php

namespace App\Services\Quiz;

use App\Exceptions\QuizNotAvailableException;
use App\Models\Quiz;

class QuizPublishService
{
    public function publish(Quiz $quiz): void
    {
        if ($quiz->total_questions < 1) {
            throw new QuizNotAvailableException('A quiz must have at least one question before publishing.');
        }

        $quiz->update(['status' => 'published']);
    }

    public function archive(Quiz $quiz): void
    {
        $quiz->update(['status' => 'archived']);
    }

    public function draft(Quiz $quiz): void
    {
        $quiz->update(['status' => 'draft']);
    }

    public function publishDueScheduled(): int
    {
        $count = 0;

        Quiz::query()
            ->where('status', 'scheduled')
            ->whereNotNull('start_at')
            ->where('start_at', '<=', now())
            ->where('total_questions', '>', 0)
            ->each(function (Quiz $quiz) use (&$count) {
                $this->publish($quiz);
                $count++;
            });

        return $count;
    }

    public function canAttempt(Quiz $quiz): bool
    {
        return $this->attemptBlockReason($quiz) === null;
    }

    public function attemptBlockReason(Quiz $quiz): ?string
    {
        if ($quiz->status !== 'published') {
            return 'not_published';
        }

        if ($quiz->start_at && now()->lt($quiz->start_at)) {
            return 'before_start';
        }

        if ($quiz->end_at && now()->gt($quiz->end_at)) {
            return 'after_end';
        }

        return null;
    }

    public function effectiveDurationSeconds(Quiz $quiz): int
    {
        $duration = $quiz->duration_minutes ? (int) $quiz->duration_minutes * 60 : 0;

        if (! $quiz->end_at || now()->gte($quiz->end_at)) {
            return $duration;
        }

        $remaining = max(0, (int) ceil(now()->floatDiffInSeconds($quiz->end_at, false)));

        if ($duration === 0) {
            return $remaining;
        }

        return min($duration, $remaining);
    }
}
