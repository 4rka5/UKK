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
        User::create([
            'username' => 'admin',
            'fullname' => 'Administrator',
            'email' => 'admin@example.com',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        // Create Team Lead user
        User::create([
            'username' => 'teamlead',
            'fullname' => 'Team Lead',
            'email' => 'lead@example.com',
            'password' => Hash::make('lead123'),
            'role' => 'team_lead',
            'status' => 'active',
        ]);

        // Create Developer user
        User::create([
            'username' => 'developer',
            'fullname' => 'Developer',
            'email' => 'dev@example.com',
            'password' => Hash::make('dev123'),
            'role' => 'developer',
            'status' => 'active',
        ]);

        // Create Designer user
        User::create([
            'username' => 'designer',
            'fullname' => 'Designer',
            'email' => 'des@example.com',
            'password' => Hash::make('des123'),
            'role' => 'designer',
            'status' => 'active',
        ]);

        // Seed project data with boards, cards, assignments
        $this->call([
            ProjectDataSeeder::class,
        ]);
    }
}
