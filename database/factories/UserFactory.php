<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        static $id = 1;

        return [
            'name' => fake()->name(),
            'email' => 'login' . $id++ . '@example.com',
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'preferred_frequency_per_week' => fake()->numberBetween(1, 5),
            'preferred_frequency_per_month' => fake()->numberBetween(4, 20),
            'min_session_length_hours' => fake()->numberBetween(1, 2),
            'max_session_length_hours' => fake()->numberBetween(2, 4),
            'phone' => fake()->phoneNumber(),
            'phone_visible' => fake()->boolean(),
            'email_visible' => fake()->boolean(),
            'is_active' => true,
            'onboarding_completed' => true,
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
            'onboarding_completed' => false,
        ]);
    }

    /**
     * Create an inactive user.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
