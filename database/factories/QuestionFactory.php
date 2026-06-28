<?php

namespace Database\Factories;

use App\Models\Quiz;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuestionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'quiz_id'        => Quiz::factory(),
            'type'           => 'mcq_single',
            'content'        => $this->faker->sentence() . '?',
            'marks'          => 1.00,
            'negative_marks' => 0.00,
            'sort_order'     => 0,
            'is_mandatory'   => true,
        ];
    }
}
