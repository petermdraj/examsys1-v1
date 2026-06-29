<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'name'              => fake()->name(),
            'email'             => fake()->unique()->safeEmail(),
            'password'          => static::$password ??= Hash::make('password'),
            'remember_token'    => Str::random(10),
            'role'              => 'student',
            'is_active'         => true,
            'email_verified_at' => now(),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function create($attributes = [], ?Model $parent = null)
    {
        return User::unguarded(fn () => parent::create($attributes, $parent));
    }

    public function make($attributes = [], ?Model $parent = null)
    {
        return User::unguarded(fn () => parent::make($attributes, $parent));
    }
}
