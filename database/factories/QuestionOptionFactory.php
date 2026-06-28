<?php

namespace Database\Factories;

use App\Models\Question;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuestionOptionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'question_id' => Question::factory(),
            'content'     => $this->faker->sentence(3),
            'is_correct'  => false,
            'sort_order'  => 0,
        ];
    }
}
