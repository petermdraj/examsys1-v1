<?php

namespace Database\Factories;

use App\Models\Attempt;
use App\Models\Quiz;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CertificateFactory extends Factory
{
    public function definition(): array
    {
        return [
            'attempt_id' => Attempt::factory(),
            'user_id'    => User::factory(),
            'quiz_id'    => Quiz::factory(),
            'uuid'       => (string) Str::uuid(),
            'issued_at'  => now(),
        ];
    }
}
