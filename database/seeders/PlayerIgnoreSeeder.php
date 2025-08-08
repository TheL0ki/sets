<?php

namespace Database\Seeders;

use App\Models\PlayerIgnore;
use App\Models\User;
use Illuminate\Database\Seeder;

class PlayerIgnoreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all users
        $users = User::where('is_active', true)->get();
        
        if ($users->count() < 2) {
            $this->command->info('Not enough users to create ignore relationships.');
            return;
        }

        // Create some sample ignore relationships
        $ignoreRelationships = [
            [
                'ignorer_id' => $users[0]->id,
                'ignored_id' => $users[1]->id,
                'reason' => 'Different playing style',
            ],
            [
                'ignorer_id' => $users[1]->id,
                'ignored_id' => $users[2]->id ?? $users[0]->id,
                'reason' => 'Schedule conflicts',
            ],
        ];

        // Only create relationships if we have enough users
        foreach ($ignoreRelationships as $relationship) {
            if (isset($relationship['ignored_id'])) {
                PlayerIgnore::create($relationship);
            }
        }

        $this->command->info('Player ignore relationships seeded successfully.');
    }
}
