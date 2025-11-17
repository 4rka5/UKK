<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\ManagementProjectBoard;
use App\Models\ManagementProjectCard;
use App\Models\ManagementProjectSubtask;
use App\Models\ManagementProjectCardAssignment;
use Illuminate\Support\Facades\DB;

class ProjectDataSeeder extends Seeder
{
    public function run(): void
    {
        // Get users
        $leader = User::where('role', 'team_lead')->first();
        $developer = User::where('role', 'developer')->first();
        $designer = User::where('role', 'designer')->first();

        if (!$leader || !$developer || !$designer) {
            $this->command->error('Users not found! Please run AdminUserSeeder first.');
            return;
        }

        // Create Project
        $project = Project::create([
            'project_name' => 'Website E-Commerce',
            'description' => 'Proyek pembuatan website e-commerce untuk penjualan produk fashion',
            'deadline' => now()->addDays(20),
            'created_by' => $leader->id,
        ]);

        // Add Project Members
        ProjectMember::create([
            'project_id' => $project->id,
            'user_id' => $developer->id,
            'role' => 'developer',
            'joined_at' => now()->subDays(9),
        ]);

        ProjectMember::create([
            'project_id' => $project->id,
            'user_id' => $designer->id,
            'role' => 'designer',
            'joined_at' => now()->subDays(9),
        ]);

        // Create Boards
        $todoBoard = ManagementProjectBoard::create([
            'project_id' => $project->id,
            'board_name' => 'To Do',
            'description' => 'Tugas yang belum dikerjakan',
        ]);

        $inProgressBoard = ManagementProjectBoard::create([
            'project_id' => $project->id,
            'board_name' => 'In Progress',
            'description' => 'Tugas yang sedang dikerjakan',
        ]);

        $reviewBoard = ManagementProjectBoard::create([
            'project_id' => $project->id,
            'board_name' => 'Review',
            'description' => 'Tugas yang sedang direview',
        ]);

        $doneBoard = ManagementProjectBoard::create([
            'project_id' => $project->id,
            'board_name' => 'Done',
            'description' => 'Tugas yang sudah selesai',
        ]);

        // Create Cards for To Do Board
        $card1 = ManagementProjectCard::create([
            'board_id' => $todoBoard->id,
            'card_title' => 'Setup Database Schema',
            'description' => 'Membuat schema database untuk produk, kategori, dan user',
            'priority' => 'high',
            'status' => 'todo',
            'due_date' => now()->addDays(3),
            'created_by' => $leader->id,
            'estimated_hours' => 8,
            'actual_hours' => 0,
        ]);

        $card2 = ManagementProjectCard::create([
            'board_id' => $todoBoard->id,
            'card_title' => 'Design Homepage Layout',
            'description' => 'Membuat design mockup untuk halaman utama website',
            'priority' => 'high',
            'status' => 'todo',
            'due_date' => now()->addDays(5),
            'created_by' => $leader->id,
            'estimated_hours' => 6,
            'actual_hours' => 0,
        ]);

        // Create Cards for In Progress Board
        $card3 = ManagementProjectCard::create([
            'board_id' => $inProgressBoard->id,
            'card_title' => 'Implement Product Catalog API',
            'description' => 'Membuat REST API untuk menampilkan katalog produk dengan filtering dan pagination',
            'priority' => 'high',
            'status' => 'in_progress',
            'due_date' => now()->addDays(7),
            'created_by' => $leader->id,
            'estimated_hours' => 12,
            'actual_hours' => 2,
        ]);

        $card4 = ManagementProjectCard::create([
            'board_id' => $inProgressBoard->id,
            'card_title' => 'Create Product Card Component',
            'description' => 'Design komponen kartu produk yang responsive',
            'priority' => 'medium',
            'status' => 'in_progress',
            'due_date' => now()->addDays(6),
            'created_by' => $leader->id,
            'estimated_hours' => 4,
            'actual_hours' => 1,
        ]);

        // Create Cards for Review Board
        $card5 = ManagementProjectCard::create([
            'board_id' => $reviewBoard->id,
            'card_title' => 'User Authentication Module',
            'description' => 'Implementasi login, register, dan forgot password',
            'priority' => 'high',
            'status' => 'review',
            'due_date' => now()->addDays(2),
            'created_by' => $leader->id,
            'estimated_hours' => 8,
            'actual_hours' => 4,
        ]);

        // Create Cards for Done Board
        $card6 = ManagementProjectCard::create([
            'board_id' => $doneBoard->id,
            'card_title' => 'Project Setup & Configuration',
            'description' => 'Setup Laravel project, install dependencies, konfigurasi environment',
            'priority' => 'high',
            'status' => 'done',
            'due_date' => now()->subDays(5),
            'created_by' => $leader->id,
            'estimated_hours' => 5,
            'actual_hours' => 3,
        ]);

        // Assign Cards to Users
        ManagementProjectCardAssignment::create([
            'card_id' => $card1->id,
            'user_id' => $developer->id,
            'assignment_status' => 'pending',
            'assigned_at' => now(),
        ]);

        ManagementProjectCardAssignment::create([
            'card_id' => $card2->id,
            'user_id' => $designer->id,
            'assignment_status' => 'pending',
            'assigned_at' => now(),
        ]);

        ManagementProjectCardAssignment::create([
            'card_id' => $card3->id,
            'user_id' => $developer->id,
            'assignment_status' => 'in_progress',
            'assigned_at' => now()->subDays(2),
            'work_started_at' => now()->subHours(3),
            'total_work_seconds' => 7200, // 2 hours
            'is_working' => true,
        ]);

        ManagementProjectCardAssignment::create([
            'card_id' => $card4->id,
            'user_id' => $designer->id,
            'assignment_status' => 'in_progress',
            'assigned_at' => now()->subDays(1),
            'work_started_at' => now()->subHours(1),
            'total_work_seconds' => 3600, // 1 hour
            'is_working' => true,
        ]);

        ManagementProjectCardAssignment::create([
            'card_id' => $card5->id,
            'user_id' => $developer->id,
            'assignment_status' => 'review',
            'assigned_at' => now()->subDays(3),
            'total_work_seconds' => 14400, // 4 hours
            'is_working' => false,
        ]);

        ManagementProjectCardAssignment::create([
            'card_id' => $card6->id,
            'user_id' => $developer->id,
            'assignment_status' => 'completed',
            'assigned_at' => now()->subDays(10),
            'total_work_seconds' => 10800, // 3 hours
            'is_working' => false,
        ]);

        // Create Subtasks
        ManagementProjectSubtask::create([
            'card_id' => $card1->id,
            'subtask_title' => 'Create users table migration',
            'is_completed' => true,
        ]);

        ManagementProjectSubtask::create([
            'card_id' => $card1->id,
            'subtask_title' => 'Create products table migration',
            'is_completed' => false,
        ]);

        ManagementProjectSubtask::create([
            'card_id' => $card1->id,
            'subtask_title' => 'Create categories table migration',
            'is_completed' => false,
        ]);

        ManagementProjectSubtask::create([
            'card_id' => $card3->id,
            'subtask_title' => 'Setup API routes',
            'is_completed' => true,
        ]);

        ManagementProjectSubtask::create([
            'card_id' => $card3->id,
            'subtask_title' => 'Create Product controller',
            'is_completed' => true,
        ]);

        ManagementProjectSubtask::create([
            'card_id' => $card3->id,
            'subtask_title' => 'Implement filtering logic',
            'is_completed' => false,
        ]);

        ManagementProjectSubtask::create([
            'card_id' => $card3->id,
            'subtask_title' => 'Add pagination',
            'is_completed' => false,
        ]);

        ManagementProjectSubtask::create([
            'card_id' => $card5->id,
            'subtask_title' => 'Create login form',
            'is_completed' => true,
        ]);

        ManagementProjectSubtask::create([
            'card_id' => $card5->id,
            'subtask_title' => 'Create register form',
            'is_completed' => true,
        ]);

        ManagementProjectSubtask::create([
            'card_id' => $card5->id,
            'subtask_title' => 'Implement password reset',
            'is_completed' => true,
        ]);

        $this->command->info('Project data seeded successfully!');
        $this->command->info("Project: {$project->project_name}");
        $this->command->info("Boards: 4 (To Do, In Progress, Review, Done)");
        $this->command->info("Cards: 6");
        $this->command->info("Subtasks: 10");
    }
}
