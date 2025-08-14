<?php

namespace Database\Factories;

use App\Models\PadelSession;
use App\Models\SessionParticipant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SessionParticipant>
 */
class SessionParticipantFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = SessionParticipant::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'session_id' => PadelSession::factory(),
            'user_id' => User::factory(),
            'status' => SessionParticipant::STATUS_INVITED,
            'confirmed_at' => null,
        ];
    }

    /**
     * Indicate that the participant has confirmed.
     */
    public function confirmed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => SessionParticipant::STATUS_CONFIRMED,
            'confirmed_at' => now(),
        ]);
    }

    /**
     * Indicate that the participant has declined.
     */
    public function declined(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => SessionParticipant::STATUS_DECLINED,
            'confirmed_at' => null,
        ]);
    }

    /**
     * Indicate that the participant is still invited.
     */
    public function invited(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => SessionParticipant::STATUS_INVITED,
            'confirmed_at' => null,
        ]);
    }
}
