<?php

namespace App\Services\Quiz;

use App\Exceptions\PlanLimitExceededException;
use App\Models\Question;
use App\Models\User;

class PlanLimitService
{
    public function getActivePlan(User $creator): ?\App\Models\Plan
    {
        return $creator->activeSubscription()->with('plan')->first()?->plan;
    }

    /**
     * Assert the creator can create another quiz.
     *
     * @throws PlanLimitExceededException
     */
    public function assertCanCreateQuiz(User $creator): void
    {
        $plan = $this->getActivePlan($creator);

        if (! $plan || $plan->max_published_quizzes === null) {
            return;
        }

        $count = \App\Models\Quiz::where('creator_id', $creator->id)
            ->whereIn('status', ['published', 'draft', 'scheduled'])
            ->count();

        if ($count >= $plan->max_published_quizzes) {
            throw new PlanLimitExceededException(
                "Your {$plan->name} plan allows a maximum of {$plan->max_published_quizzes} quizzes. "
                . "Please upgrade your plan to create more."
            );
        }
    }

    /**
     * Assert the creator can add another question to their standalone question bank.
     *
     * @throws PlanLimitExceededException
     */
    public function assertCanAddBankQuestion(User $creator): void
    {
        $plan = $this->getActivePlan($creator);

        if (! $plan || $plan->max_questions_in_bank === null) {
            return;
        }

        // Bank questions: quiz_id IS NULL, owned by this creator
        $bankCount = Question::whereNull('quiz_id')
            ->where('creator_id', $creator->id)
            ->count();

        if ($bankCount >= $plan->max_questions_in_bank) {
            throw new PlanLimitExceededException(
                "Your {$plan->name} plan allows a maximum of {$plan->max_questions_in_bank} questions in your bank. "
                . "Please upgrade to add more."
            );
        }
    }

    /**
     * Assert the creator can add another question to the given quiz.
     * Checks the per-quiz question limit only.
     *
     * @throws PlanLimitExceededException
     */
    public function assertCanAddQuestion(User $creator, string $quizId): void
    {
        $plan = $this->getActivePlan($creator);

        if (! $plan) {
            return;
        }

        if ($plan->max_questions_per_quiz !== null) {
            $quizCount = Question::where('quiz_id', $quizId)->count();
            if ($quizCount >= $plan->max_questions_per_quiz) {
                throw new PlanLimitExceededException(
                    "Your {$plan->name} plan allows a maximum of {$plan->max_questions_per_quiz} questions per quiz. "
                    . "Please upgrade to add more."
                );
            }
        }
    }
}
