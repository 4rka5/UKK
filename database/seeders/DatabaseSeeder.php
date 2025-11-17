<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create Admin user
        User::firstOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'username' => 'admin',
                'fullname' => 'Administrator',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'status' => 'active',
            ]
        );

        // Create Team Lead user
        User::firstOrCreate(
            ['email' => 'lead@gmail.com'],
            [
                'username' => 'teamlead',
                'fullname' => 'Team Lead',
                'password' => Hash::make('lead123'),
                'role' => 'team_lead',
                'status' => 'active',
            ]
        );

        // Create Developer user
        User::firstOrCreate(
            ['email' => 'dev@gmail.com'],
            [
                'username' => 'developer',
                'fullname' => 'Developer',
                'password' => Hash::make('dev123'),
                'role' => 'developer',
                'status' => 'active',
            ]
        );

        // Create Designer user
        User::firstOrCreate(
            ['email' => 'des@gmail.com'],
            [
                'username' => 'designer',
                'fullname' => 'Designer',
                'password' => Hash::make('des123'),
                'role' => 'designer',
                'status' => 'active',
            ]
        );

        // Seed project data with boards, cards, assignments
        $this->call([
            ProjectDataSeeder::class,
        ]);
    }
}
