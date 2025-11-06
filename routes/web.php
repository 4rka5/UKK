<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Lead\DashboardController as LeadDashboardController;
use App\Http\Controllers\Lead\BoardController as LeadBoardController;
use App\Http\Controllers\Lead\CardController as LeadCardController;
use App\Http\Controllers\Member\DashboardController as MemberDashboardController;
use App\Http\Controllers\Member\CardController as MemberCardController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ProjectController;
use App\Http\Controllers\AuthController;

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login')->middleware('guest');
Route::post('/login', [AuthController::class, 'login'])->middleware('guest');

Route::get('/register', [AuthController::class, 'showRegisterForm'])->middleware('guest');
Route::post('/register', [AuthController::class, 'register'])->middleware('guest');

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth');

Route::middleware(['auth','role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('users', UserController::class)->except(['show']);
    Route::resource('projects', ProjectController::class)->except(['show']);
});

Route::middleware(['auth','role:team_lead'])->prefix('lead')->name('lead.')->group(function () {
    Route::get('/', [LeadDashboardController::class, 'index'])->name('dashboard');
    Route::resource('boards', LeadBoardController::class)->except(['show']);
    Route::resource('cards', LeadCardController::class)->except(['show']);
    Route::patch('cards/{card}/move', [LeadCardController::class, 'move'])->name('cards.move');
});

Route::middleware(['auth','role:designer,developer,team_lead,admin'])->prefix('member')->name('member.')->group(function () {
    Route::get('/', [MemberDashboardController::class, 'index'])->name('dashboard');
    Route::get('/cards/{card}', [MemberCardController::class, 'show'])->name('cards.show');
    Route::patch('/cards/{card}/status', [MemberCardController::class, 'updateStatus'])->name('cards.updateStatus');
});

Route::get('/', fn() => redirect('/login'));

