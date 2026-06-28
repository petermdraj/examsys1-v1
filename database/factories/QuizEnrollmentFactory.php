<?php

namespace Database\Factories;

use App\Models\Quiz;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuizEnrollmentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'quiz_id'     => Quiz::factory(),
            'user_id'     => User::factory(),
            'enrolled_at' => now(),
            'source'      => 'free',
        ];
    }
}
