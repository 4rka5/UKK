<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin user
        User::firstOrCreate(
            ['username' => 'admin'],
            [
                'email' => 'admin@gmail.com',
                'fullname' => 'Administrator',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'status' => 'idle',
            ]
        );

        // Team Lead user
        User::firstOrCreate(
            ['username' => 'leader'],
            [
                'email' => 'lead@gmail.com',
                'fullname' => 'Team Leader',
                'password' => Hash::make('leader123'),
                'role' => 'team_lead',
                'status' => 'idle',
            ]
        );

        // Developer user
        User::firstOrCreate(
            ['username' => 'developer'],
            [
                'email' => 'dev@gmail.com',
                'fullname' => 'Developer User',
                'password' => Hash::make('developer123'),
                'role' => 'developer',
                'status' => 'idle',
            ]
        );

        // Designer user
        User::firstOrCreate(
            ['username' => 'designer'],
            [
                'email' => 'des@gmail.com',
                'fullname' => 'Designer User',
                'password' => Hash::make('designer123'),
                'role' => 'designer',
                'status' => 'idle',
            ]
        );
    }
}
