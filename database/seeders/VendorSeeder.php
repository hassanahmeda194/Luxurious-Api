<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\User;
use App\Models\UserInfo;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class VendorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 0; $i < 3; $i++) { 
            $user = User::create([
                'name' => fake()->name(),
                'email' => fake()->safeEmail(),
                'password' => Hash::make('password'),
                'role_id' => 2
            ]);

            $user_info = UserInfo::create([
                'username' => fake()->userName(),
                'gender' => fake()->randomElement(['Male', 'Female', 'Other']),
                'phone_number' => fake()->phoneNumber(),
                'address' => fake()->address(),
                'country' => fake()->country(),
                'city' => fake()->city(),
                'zip_code' => rand(1000, 9999),
                'state' => fake()->state(),
                'user_id' => $user->id
            ]);

            for ($j = 0; $j < rand(3, 5); $j++) { 
                Service::create([
                    'name' => fake()->text(rand(5, 8)), 
                    'description' => fake()->paragraph(),
                    'price' => rand(100, 999),
                    'vendor_id' => $user->id
                ]);
            }
        }
    }
}
