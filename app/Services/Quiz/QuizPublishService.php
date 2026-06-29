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

    public function canAttempt(Quiz $quiz): bool
    {
        if ($quiz->status !== 'published') {
            return false;
        }

        if ($quiz->start_at && now()->lt($quiz->start_at)) {
            return false;
        }

        if ($quiz->end_at && now()->gt($quiz->end_at)) {
            return false;
        }

        return true;
    }
}
