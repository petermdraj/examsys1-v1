<?php

namespace Database\Factories;

use App\Models\Quiz;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id'             => User::factory(),
            'quiz_id'             => Quiz::factory(),
            'amount'              => 99.00,
            'currency'            => 'INR',
            'platform_commission' => 19.80,
            'creator_earning'     => 79.20,
            'status'              => 'pending',
            'gateway'             => 'razorpay',
        ];
    }
}
