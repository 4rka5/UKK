<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Project;
use App\Models\ManagementProjectBoard;
use App\Models\ManagementProjectCard;
use App\Models\ManagementProjectSubtask;
use Illuminate\Support\Facades\Hash;

class TaskAssignmentSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Buat users jika belum ada
        $admin = User::firstOrCreate(
            ['username' => 'admin'],
            [
                'fullname' => 'System Administrator',
                'email' => 'admin@example.com',
                'password' => Hash::make('password'),
                'role' => 'admin'
            ]
        );

        $teamLead = User::firstOrCreate(
            ['username' => 'teamlead'],
            [
                'fullname' => 'Team Lead',
                'email' => 'teamlead@example.com',
                'password' => Hash::make('password'),
                'role' => 'team_lead'
            ]
        );

        $designer = User::firstOrCreate(
            ['username' => 'desainer'],
            [
                'fullname' => 'UI/UX Designer',
                'email' => 'designer@example.com',
                'password' => Hash::make('password'),
                'role' => 'designer'
            ]
        );

        $developer = User::firstOrCreate(
            ['username' => 'developer'],
            [
                'fullname' => 'Full Stack Developer',
                'email' => 'developer@example.com',
                'password' => Hash::make('password'),
                'role' => 'developer'
            ]
        );

        // 2. Admin buat project dan assign ke team lead
        $project = Project::firstOrCreate(
            ['project_name' => 'E-Commerce Website'],
            [
                'description' => 'Build a modern e-commerce platform with admin panel',
                'created_by' => $admin->id,
                'assigned_to' => $teamLead->id, // Assign ke team lead
                'deadline' => now()->addMonths(3)
            ]
        );

        // 3. Buat boards
        $designBoard = ManagementProjectBoard::firstOrCreate(
            [
                'project_id' => $project->id,
                'board_name' => 'Design Tasks'
            ],
            [
                'description' => 'UI/UX Design tasks'
            ]
        );

        $devBoard = ManagementProjectBoard::firstOrCreate(
            [
                'project_id' => $project->id,
                'board_name' => 'Development Tasks'
            ],
            [
                'description' => 'Frontend and Backend development'
            ]
        );

        // 4. Buat tasks untuk DESIGNER
        $designTasks = [
            [
                'card_title' => 'Create Homepage Wireframe',
                'description' => 'Design wireframe for homepage including hero section, product grid, and footer',
                'priority' => 'high',
                'status' => 'in_progress',
                'estimated_hours' => 8
            ],
            [
                'card_title' => 'Design Product Detail Page',
                'description' => 'Create mockup for product detail page with image gallery and reviews section',
                'priority' => 'high',
                'status' => 'todo',
                'estimated_hours' => 6
            ],
            [
                'card_title' => 'Create Shopping Cart UI',
                'description' => 'Design shopping cart interface with quantity controls and checkout button',
                'priority' => 'medium',
                'status' => 'todo',
                'estimated_hours' => 4
            ],
            [
                'card_title' => 'Design Admin Dashboard',
                'description' => 'Create admin panel mockup with statistics cards and data tables',
                'priority' => 'low',
                'status' => 'todo',
                'estimated_hours' => 10
            ]
        ];

        foreach ($designTasks as $taskData) {
            $card = ManagementProjectCard::create([
                'board_id' => $designBoard->id,
                'card_title' => $taskData['card_title'],
                'description' => $taskData['description'],
                'priority' => $taskData['priority'],
                'status' => $taskData['status'],
                'estimated_hours' => $taskData['estimated_hours'],
                'due_date' => now()->addDays(rand(7, 30)),
                'created_by' => $teamLead->id,
                'assigned_to' => $designer->id // Assign ke designer
            ]);

            // Tambahkan subtasks
            if ($taskData['card_title'] == 'Create Homepage Wireframe') {
                ManagementProjectSubtask::create([
                    'card_id' => $card->id,
                    'subtask_title' => 'Hero section design',
                    'description' => 'Design hero section with CTA',
                    'status' => 'done',
                    'estimated_hours' => 2
                ]);
                ManagementProjectSubtask::create([
                    'card_id' => $card->id,
                    'subtask_title' => 'Product grid layout',
                    'description' => 'Create responsive product grid',
                    'status' => 'todo',
                    'estimated_hours' => 3
                ]);
            }
        }

        // 5. Buat tasks untuk DEVELOPER
        $devTasks = [
            [
                'card_title' => 'Setup Laravel Project',
                'description' => 'Initialize Laravel project with authentication and database structure',
                'priority' => 'high',
                'status' => 'done',
                'estimated_hours' => 4,
                'actual_hours' => 5
            ],
            [
                'card_title' => 'Create Product API',
                'description' => 'Build RESTful API for product CRUD operations with validation',
                'priority' => 'high',
                'status' => 'in_progress',
                'estimated_hours' => 8
            ],
            [
                'card_title' => 'Implement Shopping Cart',
                'description' => 'Develop shopping cart functionality with add/remove/update quantity',
                'priority' => 'medium',
                'status' => 'todo',
                'estimated_hours' => 10
            ],
            [
                'card_title' => 'Payment Gateway Integration',
                'description' => 'Integrate payment gateway (Stripe/Midtrans) for checkout process',
                'priority' => 'high',
                'status' => 'todo',
                'estimated_hours' => 12
            ],
            [
                'card_title' => 'Setup Email Notifications',
                'description' => 'Configure email service for order confirmations and notifications',
                'priority' => 'low',
                'status' => 'todo',
                'estimated_hours' => 4
            ]
        ];

        foreach ($devTasks as $taskData) {
            $card = ManagementProjectCard::create([
                'board_id' => $devBoard->id,
                'card_title' => $taskData['card_title'],
                'description' => $taskData['description'],
                'priority' => $taskData['priority'],
                'status' => $taskData['status'],
                'estimated_hours' => $taskData['estimated_hours'],
                'actual_hours' => $taskData['actual_hours'] ?? null,
                'due_date' => now()->addDays(rand(7, 45)),
                'created_by' => $teamLead->id,
                'assigned_to' => $developer->id // Assign ke developer
            ]);

            // Tambahkan subtasks
            if ($taskData['card_title'] == 'Create Product API') {
                ManagementProjectSubtask::create([
                    'card_id' => $card->id,
                    'subtask_title' => 'Create Product model and migration',
                    'description' => 'Setup database structure',
                    'status' => 'done',
                    'estimated_hours' => 2
                ]);
                ManagementProjectSubtask::create([
                    'card_id' => $card->id,
                    'subtask_title' => 'Build API controller',
                    'description' => 'Implement CRUD endpoints',
                    'status' => 'done',
                    'estimated_hours' => 3
                ]);
                ManagementProjectSubtask::create([
                    'card_id' => $card->id,
                    'subtask_title' => 'Add validation rules',
                    'description' => 'Validate request data',
                    'status' => 'in_progress',
                    'estimated_hours' => 1
                ]);
            }
        }

        $this->command->info('✅ Sample data created successfully!');
        $this->command->info('');
        $this->command->info('👥 Users:');
        $this->command->info('   Admin: admin / password');
        $this->command->info('   Team Lead: teamlead / password');
        $this->command->info('   Designer: desainer / password');
        $this->command->info('   Developer: developer / password');
        $this->command->info('');
        $this->command->info('📊 Hierarchy:');
        $this->command->info('   Admin creates Project → assigns to Team Lead');
        $this->command->info('   Team Lead creates Boards & Cards → assigns to Members');
        $this->command->info('   Members work on assigned tasks');
        $this->command->info('');
        $this->command->info('📊 Statistics:');
        $this->command->info('   Projects: 1 (created by admin, assigned to team lead)');
        $this->command->info('   Boards: 2 (created by team lead)');
        $this->command->info('   Design Tasks: 4 (assigned to designer)');
        $this->command->info('   Development Tasks: 5 (assigned to developer)');
    }
}
