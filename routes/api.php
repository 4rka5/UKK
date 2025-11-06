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
    Route::get('/dashboard/stats', function (Request $request) {
        $user = $request->user();
        $stats = [];

        if ($user->role === 'admin') {
            $stats = [
                'total_users' => \App\Models\User::count(),
                'total_projects' => \App\Models\Project::count(),
                'total_boards' => \App\Models\ManagementProjectBoard::count(),
                'total_cards' => \App\Models\ManagementProjectCard::count(),
            ];
        } elseif ($user->role === 'team_lead') {
            $stats = [
                'my_projects' => \App\Models\Project::where('created_by', $user->id)->count(),
                'my_boards' => \App\Models\ManagementProjectBoard::whereHas('project', function($q) use ($user) {
                    $q->where('created_by', $user->id);
                })->count(),
                'total_cards' => \App\Models\ManagementProjectCard::whereHas('board.project', function($q) use ($user) {
                    $q->where('created_by', $user->id);
                })->count(),
                'cards_by_status' => \App\Models\ManagementProjectCard::whereHas('board.project', function($q) use ($user) {
                    $q->where('created_by', $user->id);
                })->selectRaw('status, count(*) as count')->groupBy('status')->get()
            ];
        } else {
            $stats = [
                'assigned_cards' => \App\Models\ManagementProjectCard::whereHas('assignees', function($q) use ($user) {
                    $q->where('user_id', $user->id);
                })->count(),
                'cards_by_status' => \App\Models\ManagementProjectCard::whereHas('assignees', function($q) use ($user) {
                    $q->where('user_id', $user->id);
                })->selectRaw('status, count(*) as count')->groupBy('status')->get(),
                'cards_by_priority' => \App\Models\ManagementProjectCard::whereHas('assignees', function($q) use ($user) {
                    $q->where('user_id', $user->id);
                })->selectRaw('priority, count(*) as count')->groupBy('priority')->get()
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    });

    // My Assigned Cards (for members)
    Route::get('/my-cards', function (Request $request) {
        $user = $request->user();
        
        $cards = \App\Models\ManagementProjectCard::whereHas('assignees', function($q) use ($user) {
            $q->where('user_id', $user->id);
        })->with(['board.project', 'assignees', 'subtasks'])->get();

        return response()->json([
            'success' => true,
            'data' => $cards
        ]);
    });
});
