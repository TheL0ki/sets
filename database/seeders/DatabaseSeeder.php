<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->count(4)->create();
        // Insert sample availabilities for all users
        $users = User::all();

        $availabilities = [
            [
                'start_time' => now()->addDays(1)->setTime(20, 0),
                'end_time' => now()->addDays(1)->setTime(20, 30),
            ],
            [
                'start_time' => now()->addDays(1)->setTime(20, 30),
                'end_time' => now()->addDays(1)->setTime(21, 0),
            ],
            [
                'start_time' => now()->addDays(1)->setTime(21, 0),
                'end_time' => now()->addDays(1)->setTime(21, 30),
            ],
            [
                'start_time' => now()->addDays(1)->setTime(21, 30),
                'end_time' => now()->addDays(1)->setTime(22, 0),
            ],
        ];

        foreach ($users as $index => $user) {
            foreach ($availabilities as $availability) {
                \DB::table('availabilities')->insert([
                    'user_id' => $user->id,
                    'start_time' => $availability['start_time'],
                    'end_time' => $availability['end_time'],
                'created_at' => now(),
                'updated_at' => now(),
                ]);
            }
        }
    }
}
