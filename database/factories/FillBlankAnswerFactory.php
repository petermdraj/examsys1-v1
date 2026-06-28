<?php

namespace Database\Factories;

use App\Models\Question;
use Illuminate\Database\Eloquent\Factories\Factory;

class FillBlankAnswerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'question_id' => Question::factory(),
            'answer'      => $this->faker->word(),
            'is_regex'    => false,
        ];
    }
}
