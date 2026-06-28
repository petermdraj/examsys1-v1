<?php

namespace Database\Factories;

use App\Models\Quiz;
use App\Models\QuizEnrollment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttemptFactory extends Factory
{
    public function definition(): array
    {
        return [
            'quiz_id'        => Quiz::factory(),
            'user_id'        => User::factory(),
            'enrollment_id'  => QuizEnrollment::factory(),
            'attempt_number' => 1,
            'status'         => 'in_progress',
            'started_at'     => now()->subMinutes(5),
        ];
    }
}
