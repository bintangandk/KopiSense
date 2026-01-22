<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create 30 users with their profiles
        \App\Models\User::factory(30)->create()->each(function ($user) {
            $user->profile()->create(
                \App\Models\UserProfile::factory()->make()->toArray()
            );
        });
    }
}
