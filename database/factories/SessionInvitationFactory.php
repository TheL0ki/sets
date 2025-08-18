<?php

namespace Database\Factories;

use App\Models\PadelSession;
use App\Models\SessionInvitation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SessionInvitation>
 */
class SessionInvitationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = SessionInvitation::class;

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
            'status' => SessionInvitation::STATUS_PENDING,
            'responded_at' => null,
        ];
    }

    /**
     * Indicate that the invitation is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => SessionInvitation::STATUS_PENDING,
            'responded_at' => null,
        ]);
    }

    /**
     * Indicate that the invitation has been accepted.
     */
    public function accepted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => SessionInvitation::STATUS_ACCEPTED,
            'responded_at' => now(),
        ]);
    }

    /**
     * Indicate that the invitation has been declined.
     */
    public function declined(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => SessionInvitation::STATUS_DECLINED,
            'responded_at' => now(),
        ]);
    }

    /**
     * Indicate that the invitation has expired.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => SessionInvitation::STATUS_EXPIRED,
            'responded_at' => null,
        ]);
    }
}
