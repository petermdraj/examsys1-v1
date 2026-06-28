<?php

namespace Database\Factories;

use App\Models\Attempt;
use App\Models\Question;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttemptAnswerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'attempt_id'       => Attempt::factory(),
            'question_id'      => Question::factory(),
            'selected_options' => [],
            'text_answer'      => null,
            'is_correct'       => null,
            'marks_earned'     => 0,
            'is_marked_for_review' => false,
        ];
    }
}
