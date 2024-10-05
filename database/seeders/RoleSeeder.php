<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use App\Models\UserInfo;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::insert([
            ['name' => 'Admin'],
            ['name' => 'Vendor'],
            ['name' => 'Customer'],
        ]);

        $user = User::create([
            'name' => 'Admin',
            'email' => 'admin@gmail.com',
            "password" => Hash::make('password'),
            'role_id' => 1
        ]);
        $user_info = UserInfo::create([
            'username' => fake()->userName(),
            'gender' => fake()->randomElement(['Male', 'Female', 'Other']),
            'phone_number' => fake()->phoneNumber(),
            'address' => fake()->address(),
            'country' => fake()->country(),
            'city' => fake()->city(),
            'zip_code' => rand(1000, 2000),
            'state' => fake()->state(),
            'user_id' => $user->id
        ]);
    }
}
