<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\BoardController;
use App\Http\Controllers\Api\CardController;
use App\Http\Controllers\Api\SubtaskController;
use App\Http\Controllers\Api\CommentsController;

// Public Routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected Routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);

    // Projects
    Route::apiResource('projects', ProjectController::class);

    // Boards (nested under projects)
    Route::apiResource('projects.boards', BoardController::class);

    // Cards (nested under boards)
    Route::get('/boards/{board}/cards', [CardController::class, 'index']);
    Route::post('/boards/{board}/cards', [CardController::class, 'store']);
    Route::get('/boards/{board}/cards/{card}', [CardController::class, 'show']);
    Route::put('/boards/{board}/cards/{card}', [CardController::class, 'update']);
    Route::delete('/boards/{board}/cards/{card}', [CardController::class, 'destroy']);

    // Card Actions
    Route::post('/cards/{card}/assign', [CardController::class, 'assignUser']);
    Route::delete('/cards/{card}/unassign/{user}', [CardController::class, 'unassignUser']);
    Route::put('/cards/{card}/status', [CardController::class, 'updateStatus']);

    // Get single card (without board_id) - untuk Flutter
    Route::get('/cards/{card}', [CardController::class, 'showCard']);

    // Get my assigned tasks (untuk member)
    Route::get('/my-tasks', [CardController::class, 'myTasks']);

    // Subtasks - Update untuk bisa akses langsung
    Route::put('/subtasks/{subtask}', [SubtaskController::class, 'updateSubtask']);

    // Subtasks (nested under cards)
    Route::get('/cards/{card}/subtasks', [SubtaskController::class, 'index']);
    Route::post('/cards/{card}/subtasks', [SubtaskController::class, 'store']);
    Route::get('/cards/{card}/subtasks/{subtask}', [SubtaskController::class, 'show']);
    Route::put('/cards/{card}/subtasks/{subtask}', [SubtaskController::class, 'update']);
    Route::delete('/cards/{card}/subtasks/{subtask}', [SubtaskController::class, 'destroy']);

    // Comments (nested under cards)
    Route::get('/cards/{card}/comments', [CommentsController::class, 'index']);
    Route::post('/cards/{card}/comments', [CommentsController::class, 'store']);
    Route::get('/cards/{card}/comments/{comment}', [CommentsController::class, 'show']);
    Route::put('/cards/{card}/comments/{comment}', [CommentsController::class, 'update']);
    Route::delete('/cards/{card}/comments/{comment}', [CommentsController::class, 'destroy']);

    // Dashboard Stats
    Route::get('/dashboard', function (Request $request) {
        $user = $request->user();
        $stats = [];

        if ($user->role === 'admin') {
            // Admin: statistik project yang dia buat
            $stats = [
                'total_projects' => \App\Models\Project::where('created_by', $user->id)->count(),
                'assigned_projects' => \App\Models\Project::where('created_by', $user->id)->whereNotNull('assigned_to')->count(),
                'unassigned_projects' => \App\Models\Project::where('created_by', $user->id)->whereNull('assigned_to')->count(),
                'total_team_leads' => \App\Models\User::where('role', 'team_lead')->count(),
            ];
        } elseif ($user->role === 'team_lead') {
            // Team Lead: statistik project yang di-assign ke mereka
            $assignedProjects = \App\Models\Project::where('assigned_to', $user->id)->pluck('id');

            $stats = [
                'assigned_projects' => $assignedProjects->count(),
                'total_boards' => \App\Models\ManagementProjectBoard::whereIn('project_id', $assignedProjects)->count(),
                'total_cards' => \App\Models\ManagementProjectCard::whereHas('board', function($q) use ($assignedProjects) {
                    $q->whereIn('project_id', $assignedProjects);
                })->count(),
                'assigned_cards' => \App\Models\ManagementProjectCard::whereHas('board', function($q) use ($assignedProjects) {
                    $q->whereIn('project_id', $assignedProjects);
                })->whereNotNull('assigned_to')->count(),
            ];
        } else {
            // Member (designer/developer) - tampilkan task yang di-assign ke mereka
            $stats = [
                'assigned_tasks' => \App\Models\ManagementProjectCard::where('assigned_to', $user->id)->count(),
                'todo' => \App\Models\ManagementProjectCard::where('assigned_to', $user->id)->where('status', 'todo')->count(),
                'in_progress' => \App\Models\ManagementProjectCard::where('assigned_to', $user->id)->where('status', 'in_progress')->count(),
                'done' => \App\Models\ManagementProjectCard::where('assigned_to', $user->id)->where('status', 'done')->count(),
                'high_priority' => \App\Models\ManagementProjectCard::where('assigned_to', $user->id)->where('priority', 'high')->count(),
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    });
});
