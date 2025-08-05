<?php

namespace Database\Seeders;

use App\Models\Availability;
use App\Models\MatchPlayer;
use App\Models\PadelMatch;
use App\Models\PadelSession;
use App\Models\SessionParticipant;
use App\Models\User;
use Illuminate\Database\Seeder;

class TestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create test users
        $users = [
            User::create([
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'password' => bcrypt('password'),
                'skill_level' => 'intermediate',
                'preferred_frequency_per_week' => 2,
                'phone' => '+1234567890',
                'is_active' => true,
            ]),
            User::create([
                'name' => 'Jane Smith',
                'email' => 'jane@example.com',
                'password' => bcrypt('password'),
                'skill_level' => 'advanced',
                'preferred_frequency_per_week' => 3,
                'phone' => '+1234567891',
                'is_active' => true,
            ]),
            User::create([
                'name' => 'Bob Johnson',
                'email' => 'bob@example.com',
                'password' => bcrypt('password'),
                'skill_level' => 'beginner',
                'preferred_frequency_per_week' => 1,
                'phone' => '+1234567892',
                'is_active' => true,
            ]),
            User::create([
                'name' => 'Alice Brown',
                'email' => 'alice@example.com',
                'password' => bcrypt('password'),
                'skill_level' => 'intermediate',
                'preferred_frequency_per_week' => 2,
                'phone' => '+1234567893',
                'is_active' => true,
            ]),
        ];

        // Create test availabilities
        foreach ($users as $user) {
            Availability::create([
                'user_id' => $user->id,
                'start_time' => now()->addDays(1)->setTime(18, 0), // Tomorrow 6 PM
                'end_time' => now()->addDays(1)->setTime(20, 0),   // Tomorrow 8 PM
                'is_available' => true,
                'notes' => 'Available for evening session',
            ]);
        }

        // Create a test session
        $session = PadelSession::create([
            'start_time' => now()->addDays(1)->setTime(18, 0),
            'end_time' => now()->addDays(1)->setTime(20, 0),
            'location' => 'Padel Court 1',
            'status' => PadelSession::STATUS_CONFIRMED,
            'notes' => 'Evening doubles session',
            'created_by' => $users[0]->id,
            'max_players' => 8,
        ]);

        // Add participants to the session
        foreach ($users as $user) {
            SessionParticipant::create([
                'session_id' => $session->id,
                'user_id' => $user->id,
                'status' => SessionParticipant::STATUS_CONFIRMED,
                'confirmed_at' => now(),
            ]);
        }

        // Create a test match within the session
        $match = PadelMatch::create([
            'session_id' => $session->id,
            'match_number' => 1,
            'team_a_score' => 6,
            'team_b_score' => 4,
            'status' => PadelMatch::STATUS_COMPLETED,
            'started_at' => now()->addDays(1)->setTime(18, 0),
            'completed_at' => now()->addDays(1)->setTime(19, 30),
        ]);

        // Add players to the match
        MatchPlayer::create([
            'match_id' => $match->id,
            'user_id' => $users[0]->id,
            'team' => MatchPlayer::TEAM_A,
            'confirmed_at' => now(),
        ]);

        MatchPlayer::create([
            'match_id' => $match->id,
            'user_id' => $users[1]->id,
            'team' => MatchPlayer::TEAM_A,
            'confirmed_at' => now(),
        ]);

        MatchPlayer::create([
            'match_id' => $match->id,
            'user_id' => $users[2]->id,
            'team' => MatchPlayer::TEAM_B,
            'confirmed_at' => now(),
        ]);

        MatchPlayer::create([
            'match_id' => $match->id,
            'user_id' => $users[3]->id,
            'team' => MatchPlayer::TEAM_B,
            'confirmed_at' => now(),
        ]);

        $this->command->info('Test data seeded successfully!');
        $this->command->info('Created ' . count($users) . ' users');
        $this->command->info('Created 1 session with ' . count($users) . ' participants');
        $this->command->info('Created 1 completed match with scores: 6-4');
    }
}
