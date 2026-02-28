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
        // Seed provinces and cities FIRST
        $this->call(ProvinceSeeder::class);

        // Then seed users with default password 12345678
        $this->call(UserSeeder::class);
    }
}
