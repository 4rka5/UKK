<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Project;
use App\Models\ProjectMember;
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
            $this->command->error('Users not found! Please run user seeder first.');
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
            'user_id' => $leader->id,
            'role' => 'team_lead',
            'joined_at' => now()->subDays(10),
        ]);

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

        // Create Cards (now directly linked to project, no boards)
        $card1 = ManagementProjectCard::create([
            'project_id' => $project->id,
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
            'project_id' => $project->id,
            'card_title' => 'Design Homepage Layout',
            'description' => 'Membuat design mockup untuk halaman utama website',
            'priority' => 'high',
            'status' => 'todo',
            'due_date' => now()->addDays(5),
            'created_by' => $leader->id,
            'estimated_hours' => 6,
            'actual_hours' => 0,
        ]);

        $card3 = ManagementProjectCard::create([
            'project_id' => $project->id,
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
            'project_id' => $project->id,
            'card_title' => 'Create Product Card Component',
            'description' => 'Design komponen kartu produk yang responsive',
            'priority' => 'medium',
            'status' => 'in_progress',
            'due_date' => now()->addDays(6),
            'created_by' => $leader->id,
            'estimated_hours' => 4,
            'actual_hours' => 1,
        ]);

        $card5 = ManagementProjectCard::create([
            'project_id' => $project->id,
            'card_title' => 'User Authentication Module',
            'description' => 'Implementasi login, register, dan forgot password',
            'priority' => 'high',
            'status' => 'todo',
            'due_date' => now()->addDays(2),
            'created_by' => $leader->id,
            'estimated_hours' => 8,
            'actual_hours' => 0,
        ]);

        $card6 = ManagementProjectCard::create([
            'project_id' => $project->id,
            'card_title' => 'Project Setup & Configuration',
            'description' => 'Setup Laravel project, install dependencies, konfigurasi environment',
            'priority' => 'high',
            'status' => 'done',
            'due_date' => now()->subDays(5),
            'created_by' => $leader->id,
            'estimated_hours' => 5,
            'actual_hours' => 3,
        ]);

        $card7 = ManagementProjectCard::create([
            'project_id' => $project->id,
            'card_title' => 'Shopping Cart Functionality',
            'description' => 'Implementasi fitur keranjang belanja dengan add, update, delete items',
            'priority' => 'high',
            'status' => 'todo',
            'due_date' => now()->addDays(10),
            'created_by' => $leader->id,
            'estimated_hours' => 10,
            'actual_hours' => 0,
        ]);

        $card8 = ManagementProjectCard::create([
            'project_id' => $project->id,
            'card_title' => 'Payment Gateway Integration',
            'description' => 'Integrasi payment gateway untuk proses checkout',
            'priority' => 'medium',
            'status' => 'todo',
            'due_date' => now()->addDays(15),
            'created_by' => $leader->id,
            'estimated_hours' => 8,
            'actual_hours' => 0,
        ]);

        // Assign Cards to Users
        // Developer sudah punya 1 tugas aktif (card3), jadi card1 belum di-assign
        // Designer sudah punya 1 tugas aktif (card4), jadi card2 belum di-assign
        
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
            'description' => 'Design and implement users table schema',
            'status' => 'done',
            'estimated_hours' => 2,
        ]);

        ManagementProjectSubtask::create([
            'card_id' => $card1->id,
            'subtask_title' => 'Create products table migration',
            'description' => 'Design and implement products table schema',
            'status' => 'todo',
            'estimated_hours' => 3,
        ]);

        ManagementProjectSubtask::create([
            'card_id' => $card1->id,
            'subtask_title' => 'Create categories table migration',
            'description' => 'Design and implement categories table schema',
            'status' => 'todo',
            'estimated_hours' => 3,
        ]);

        ManagementProjectSubtask::create([
            'card_id' => $card3->id,
            'subtask_title' => 'Setup API routes',
            'description' => 'Define REST API endpoints',
            'status' => 'done',
            'estimated_hours' => 2,
        ]);

        ManagementProjectSubtask::create([
            'card_id' => $card3->id,
            'subtask_title' => 'Create Product controller',
            'description' => 'Implement product controller methods',
            'status' => 'done',
            'estimated_hours' => 4,
        ]);

        ManagementProjectSubtask::create([
            'card_id' => $card3->id,
            'subtask_title' => 'Implement filtering logic',
            'description' => 'Add product filtering by category, price, etc',
            'status' => 'in_progress',
            'estimated_hours' => 3,
        ]);

        ManagementProjectSubtask::create([
            'card_id' => $card3->id,
            'subtask_title' => 'Add pagination',
            'description' => 'Implement pagination for product list',
            'status' => 'todo',
            'estimated_hours' => 3,
        ]);

        ManagementProjectSubtask::create([
            'card_id' => $card5->id,
            'subtask_title' => 'Create login form',
            'description' => 'Design and implement login UI',
            'status' => 'done',
            'estimated_hours' => 2,
        ]);

        ManagementProjectSubtask::create([
            'card_id' => $card5->id,
            'subtask_title' => 'Create register form',
            'description' => 'Design and implement register UI',
            'status' => 'done',
            'estimated_hours' => 3,
        ]);

        ManagementProjectSubtask::create([
            'card_id' => $card5->id,
            'subtask_title' => 'Implement password reset',
            'description' => 'Add forgot password functionality',
            'status' => 'done',
            'estimated_hours' => 3,
        ]);

        // Subtasks for card2 (Design Homepage)
        ManagementProjectSubtask::create([
            'card_id' => $card2->id,
            'subtask_title' => 'Create wireframe',
            'description' => 'Design homepage wireframe mockup',
            'status' => 'todo',
            'estimated_hours' => 2,
        ]);

        ManagementProjectSubtask::create([
            'card_id' => $card2->id,
            'subtask_title' => 'Design hero section',
            'description' => 'Create hero banner with CTA',
            'status' => 'todo',
            'estimated_hours' => 2,
        ]);

        ManagementProjectSubtask::create([
            'card_id' => $card2->id,
            'subtask_title' => 'Design product showcase',
            'description' => 'Create featured products section',
            'status' => 'todo',
            'estimated_hours' => 2,
        ]);

        // Subtasks for card4 (Product Card Component)
        ManagementProjectSubtask::create([
            'card_id' => $card4->id,
            'subtask_title' => 'Design card layout',
            'description' => 'Create product card UI design',
            'status' => 'done',
            'estimated_hours' => 1,
        ]);

        ManagementProjectSubtask::create([
            'card_id' => $card4->id,
            'subtask_title' => 'Add responsive behavior',
            'description' => 'Make card responsive for mobile/tablet',
            'status' => 'in_progress',
            'estimated_hours' => 2,
        ]);

        ManagementProjectSubtask::create([
            'card_id' => $card4->id,
            'subtask_title' => 'Add hover effects',
            'description' => 'Implement interactive hover animations',
            'status' => 'todo',
            'estimated_hours' => 1,
        ]);

        // Subtasks for card7 (Shopping Cart)
        ManagementProjectSubtask::create([
            'card_id' => $card7->id,
            'subtask_title' => 'Create cart model and migration',
            'description' => 'Setup cart database schema',
            'status' => 'todo',
            'estimated_hours' => 2,
        ]);

        ManagementProjectSubtask::create([
            'card_id' => $card7->id,
            'subtask_title' => 'Implement add to cart',
            'description' => 'Create functionality to add items to cart',
            'status' => 'todo',
            'estimated_hours' => 3,
        ]);

        ManagementProjectSubtask::create([
            'card_id' => $card7->id,
            'subtask_title' => 'Implement update quantity',
            'description' => 'Allow users to change item quantities',
            'status' => 'todo',
            'estimated_hours' => 2,
        ]);

        ManagementProjectSubtask::create([
            'card_id' => $card7->id,
            'subtask_title' => 'Implement remove from cart',
            'description' => 'Allow users to delete items from cart',
            'status' => 'todo',
            'estimated_hours' => 2,
        ]);

        ManagementProjectSubtask::create([
            'card_id' => $card7->id,
            'subtask_title' => 'Calculate total price',
            'description' => 'Implement cart total calculation logic',
            'status' => 'todo',
            'estimated_hours' => 1,
        ]);

        // Subtasks for card8 (Payment Gateway)
        ManagementProjectSubtask::create([
            'card_id' => $card8->id,
            'subtask_title' => 'Research payment providers',
            'description' => 'Compare Midtrans, Xendit, and other options',
            'status' => 'todo',
            'estimated_hours' => 2,
        ]);

        ManagementProjectSubtask::create([
            'card_id' => $card8->id,
            'subtask_title' => 'Setup payment API credentials',
            'description' => 'Register and configure payment gateway',
            'status' => 'todo',
            'estimated_hours' => 1,
        ]);

        ManagementProjectSubtask::create([
            'card_id' => $card8->id,
            'subtask_title' => 'Implement payment integration',
            'description' => 'Integrate payment gateway SDK',
            'status' => 'todo',
            'estimated_hours' => 4,
        ]);

        ManagementProjectSubtask::create([
            'card_id' => $card8->id,
            'subtask_title' => 'Handle payment callbacks',
            'description' => 'Process success/failed payment notifications',
            'status' => 'todo',
            'estimated_hours' => 1,
        ]);

        $this->command->info('✓ Project data seeded successfully!');
        $this->command->info("  Project: {$project->project_name}");
        $this->command->info("  Members: 3 (Team Lead, Developer, Designer)");
        $this->command->info("  Cards: 8 (4 Todo/Unassigned, 2 In Progress/Assigned, 1 Done, 1 Todo)");
        $this->command->info("  - Card 1 (Setup Database): UNASSIGNED - waiting for developer");
        $this->command->info("  - Card 2 (Design Homepage): UNASSIGNED - waiting for designer (3 subtasks)");
        $this->command->info("  - Card 3 (Product API): ASSIGNED to Developer (In Progress, 4 subtasks)");
        $this->command->info("  - Card 4 (Product Component): ASSIGNED to Designer (In Progress, 3 subtasks)");
        $this->command->info("  - Card 5 (User Auth): UNASSIGNED - available (3 subtasks)");
        $this->command->info("  - Card 6 (Project Setup): ASSIGNED to Developer (Done)");
        $this->command->info("  - Card 7 (Shopping Cart): UNASSIGNED - available (5 subtasks)");
        $this->command->info("  - Card 8 (Payment Gateway): UNASSIGNED - available (4 subtasks)");
        $this->command->info("  Assignments: 3 (1 developer active, 1 designer active, 1 completed)");
        $this->command->info("  Subtasks: 25 total");
    }
}
