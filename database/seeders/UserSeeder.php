<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all available cities
        $cities = \App\Models\City::pluck('id')->toArray();
        $provinces = \App\Models\Province::pluck('id')->toArray();

        // If no cities exist, use default values
        $cityIds = !empty($cities) ? $cities : [1];
        $provinceIds = !empty($provinces) ? $provinces : [1];

        // Create 1 admin user
        $admin = User::create([
            'username' => 'admin',
            'email' => 'admin@kopisense.com',
            'password' => Hash::make('12345678'),
            'role' => 'admin',
        ]);

        UserProfile::create([
            'user_id' => $admin->id,
            'full_name' => 'Admin User',
            'nik' => '12345678910111',
            'gender' => 'Laki-laki',
            'phone' => '08123456789',
            'address' => 'Jl. Admin No. 1',
            'province_id' => $provinceIds[0],
            'city_id' => $cityIds[0],
            'postal_code' => '12345',
        ]);

        // Create 29 employee users
        for ($i = 1; $i <= 29; $i++) {
            $employee = User::create([
                'username' => 'employee' . $i,
                'email' => 'employee' . $i . '@kopisense.com',
                'password' => Hash::make('12345678'),
                'role' => 'pegawai',
            ]);

            UserProfile::create([
                'user_id' => $employee->id,
                'full_name' => 'Employee User ' . $i,
                'nik' => '11111111111' . str_pad($i, 2, '0', STR_PAD_LEFT),
                'gender' => $i % 2 === 0 ? 'Perempuan' : 'Laki-laki',
                'phone' => '0812345678' . str_pad($i, 2, '0', STR_PAD_LEFT),
                'address' => 'Jl. Employee No. ' . $i,
                'province_id' => $provinceIds[array_rand($provinceIds)],
                'city_id' => $cityIds[array_rand($cityIds)],
                'postal_code' => str_pad($i, 5, '0', STR_PAD_LEFT),
            ]);
        }
    }
}
