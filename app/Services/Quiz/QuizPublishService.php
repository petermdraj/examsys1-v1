<?php

namespace App\Services\Quiz;

use App\Exceptions\PlanLimitExceededException;
use App\Exceptions\QuizNotAvailableException;
use App\Models\Quiz;

class QuizPublishService
{
    public function publish(Quiz $quiz): void
    {
        if ($quiz->total_questions < 1) {
            throw new QuizNotAvailableException('A quiz must have at least one question before publishing.');
        }

        $creator = $quiz->creator;
        $plan    = $creator?->activeSubscription()->with('plan')->first()?->plan ?? null;

        // Enforce max published quizzes per plan
        if ($plan && $plan->max_published_quizzes !== null) {
            $currentCount = Quiz::where('creator_id', $creator->id)
                ->where('status', 'published')
                ->where('id', '!=', $quiz->id)
                ->count();

            if ($currentCount >= $plan->max_published_quizzes) {
                throw new PlanLimitExceededException(
                    "Your {$plan->name} plan allows a maximum of {$plan->max_published_quizzes} published "
                    . "quizzes. Archive or delete an existing quiz, or upgrade your plan."
                );
            }
        }

        // Enforce can_sell_paid_quizzes
        if ((float) $quiz->price > 0 && $plan && ! $plan->can_sell_paid_quizzes) {
            throw new PlanLimitExceededException(
                "Your {$plan->name} plan does not allow selling paid quizzes. "
                . "Set the price to free or upgrade your plan."
            );
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
