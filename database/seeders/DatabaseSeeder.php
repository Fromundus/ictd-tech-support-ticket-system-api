<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
        User::factory()->create([
            'username' => 'admin',
            'name' => 'Admin',
            'password' => Hash::make("password@123"),
            'role' => 'admin',
        ]);

        User::factory()->create([
            'username' => 'jenniderdelacruz',
            'name' => 'Jennifer M. Dela Cruz',
            'password' => Hash::make("password@123"),
            'role' => 'admin',
        ]);

        User::factory()->create([
            'username' => 'ralphearlcollantes',
            'name' => 'Ralph Earl L. Collantes',
            'password' => Hash::make("password@123"),
            'role' => 'user',
        ]);

        User::factory()->create([
            'username' => 'eduardstotomas',
            'name' => 'Eduard A. Sto Tomas',
            'password' => Hash::make("password@123"),
            'role' => 'user',
        ]);

        User::factory()->create([
            'username' => 'adlringuarte',
            'name' => 'Aldrin G. Guarte',
            'password' => Hash::make("password@123"),
            'role' => 'user',
        ]);

        User::factory()->create([
            'username' => 'williedulay',
            'name' => 'Willie T. Dulay Jr.',
            'password' => Hash::make("password@123"),
            'role' => 'user',
        ]);

        User::factory()->create([
            'username' => 'johncarlcueva',
            'name' => 'John Carl C. Cueva',
            'password' => Hash::make("password@123"),
            'role' => 'user',
        ]);
    }
}
