<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\BoardController;
use App\Http\Controllers\Api\CardController;
use App\Http\Controllers\Api\SubtaskController;
use App\Http\Controllers\Api\CommentsController;
use App\Http\Controllers\Api\TimeLogController;

// Auth Routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected Routes
Route::middleware('auth:sanctum')->group(function () {
    // User
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Projects
    Route::apiResource('projects', ProjectController::class);
    Route::post('/projects/{project}/members', [ProjectController::class, 'addMember']);
    Route::delete('/projects/{project}/members/{user}', [ProjectController::class, 'removeMember']);

    // Boards
    Route::apiResource('projects.boards', BoardController::class);

    // Cards
    Route::apiResource('boards.cards', CardController::class);
    Route::post('/cards/{card}/assign', [CardController::class, 'assignUser']);
    Route::delete('/cards/{card}/unassign/{user}', [CardController::class, 'unassignUser']);

    // Subtasks
    Route::apiResource('cards.subtasks', SubtaskController::class);

    // Comments
    Route::apiResource('cards.comments', CommentsController::class);

    // Time Logs
    Route::apiResource('cards.timelogs', TimeLogController::class);
    Route::post('/timelogs/{timelog}/start', [TimeLogController::class, 'startWork']);
    Route::post('/timelogs/{timelog}/stop', [TimeLogController::class, 'stopWork']);
});
