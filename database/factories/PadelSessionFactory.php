<?php

namespace Database\Factories;

use App\Models\PadelSession;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PadelSession>
 */
class PadelSessionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = PadelSession::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startTime = $this->faker->dateTimeBetween('+1 day', '+1 week');
        $endTime = clone $startTime;
        $endTime->modify('+2 hours');

        return [
            'start_time' => $startTime,
            'end_time' => $endTime,
            'location' => $this->faker->randomElement(['Padel Court 1', 'Padel Court 2', 'Sports Center', 'Tennis Club']),
            'status' => PadelSession::STATUS_PENDING,
        ];
    }

    /**
     * Indicate that the session is confirmed.
     */
    public function confirmed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PadelSession::STATUS_CONFIRMED,
        ]);
    }

    /**
     * Indicate that the session is cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PadelSession::STATUS_CANCELLED,
        ]);
    }

    /**
     * Indicate that the session is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PadelSession::STATUS_COMPLETED,
        ]);
    }

    /**
     * Set a specific start time for the session.
     */
    public function startingAt(\DateTime $startTime): static
    {
        $endTime = clone $startTime;
        $endTime->modify('+2 hours');

        return $this->state(fn (array $attributes) => [
            'start_time' => $startTime,
            'end_time' => $endTime,
        ]);
    }
}
