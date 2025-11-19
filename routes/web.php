<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Lead\DashboardController as LeadDashboardController;
use App\Http\Controllers\Lead\CardController as LeadCardController;
use App\Http\Controllers\Lead\ProjectController as LeadProjectController;
use App\Http\Controllers\Member\DashboardController as MemberDashboardController;
use App\Http\Controllers\Member\CardController as MemberCardController;
use App\Http\Controllers\Member\DeveloperController;
use App\Http\Controllers\Member\DesignerController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ProjectController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\NotificationController;

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login')->middleware('guest');
Route::post('/login', [AuthController::class, 'login'])->middleware('guest');

Route::get('/register', [AuthController::class, 'showRegisterForm'])->middleware('guest');
Route::post('/register', [AuthController::class, 'register'])->middleware('guest');

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth');

Route::middleware(['auth','role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('users', UserController::class)->except(['show']);
    Route::resource('projects', ProjectController::class)->except(['show']);
    
    // Project approval routes
    Route::get('projects/{project}/detail', [ProjectController::class, 'detail'])->name('projects.detail');
    Route::post('projects/{project}/approve', [ProjectController::class, 'approve'])->name('projects.approve');
    Route::post('projects/{project}/reject', [ProjectController::class, 'reject'])->name('projects.reject');
    
    // Project members management
    Route::get('projects/{project}/members', [ProjectController::class, 'members'])->name('projects.members');
    Route::post('projects/{project}/members', [ProjectController::class, 'addMember'])->name('projects.addMember');
    Route::delete('projects/{project}/members/{member}', [ProjectController::class, 'removeMember'])->name('projects.removeMember');
    
    // Project report
    Route::get('projects/{project}/report', [ProjectController::class, 'generateReport'])->name('projects.report');
    
    // Admin override rules (jika diperlukan)
    Route::post('override/{resourceType}/{resourceId}', [ProjectController::class, 'overrideRule'])->name('override.rule');
    
    // Reports
    Route::get('reports', [\App\Http\Controllers\Admin\ReportController::class, 'index'])->name('reports.index');
    Route::post('reports/generate', [\App\Http\Controllers\Admin\ReportController::class, 'generate'])->name('reports.generate');
});

Route::middleware(['auth','role:team_lead'])->prefix('lead')->name('lead.')->group(function () {
    Route::get('/', [LeadDashboardController::class, 'index'])->name('dashboard');
    
    // Project management for team lead
    Route::resource('projects', LeadProjectController::class)->except(['show']);
    Route::get('projects/{project}/detail', [LeadProjectController::class, 'show'])->name('projects.show');
    Route::post('projects/{project}/submit', [LeadProjectController::class, 'submitForApproval'])->name('projects.submit');
    
    // Card management
    Route::resource('cards', LeadCardController::class)->except(['show']);
    Route::patch('cards/{card}/move', [LeadCardController::class, 'move'])->name('cards.move');
    
    // Card detail, approve, reject
    Route::get('cards/{card}/detail', [LeadCardController::class, 'detail'])->name('cards.detail');
    Route::post('cards/{card}/approve', [LeadCardController::class, 'approve'])->name('cards.approve');
    Route::post('cards/{card}/reject', [LeadCardController::class, 'reject'])->name('cards.reject');
    
    // Extension approval
    Route::post('cards/{card}/extension/approve', [LeadCardController::class, 'approveExtension'])->name('cards.extension.approve');
    Route::post('cards/{card}/extension/reject', [LeadCardController::class, 'rejectExtension'])->name('cards.extension.reject');
    
    // Comments
    Route::post('cards/{card}/comment', [LeadCardController::class, 'addComment'])->name('cards.comment');
});

// Developer routes
Route::middleware(['auth','role:developer'])->prefix('developer')->name('developer.')->group(function () {
    Route::get('/', [DeveloperController::class, 'index'])->name('dashboard');
    Route::get('/tasks/{card}', [DeveloperController::class, 'show'])->name('tasks.show');
    Route::post('/tasks/{card}/progress', [DeveloperController::class, 'updateProgress'])->name('tasks.progress');
    Route::post('/tasks/{card}/upload', [DeveloperController::class, 'uploadFile'])->name('tasks.upload');
    Route::post('/tasks/{card}/blocker', [DeveloperController::class, 'reportBlocker'])->name('tasks.blocker');
    Route::post('/tasks/{card}/documentation', [DeveloperController::class, 'workDocumentation'])->name('tasks.documentation');
});

// Designer routes
Route::middleware(['auth','role:designer'])->prefix('designer')->name('designer.')->group(function () {
    Route::get('/', [DesignerController::class, 'index'])->name('dashboard');
    Route::get('/tasks/{card}', [DesignerController::class, 'show'])->name('tasks.show');
    Route::post('/tasks/{card}/upload-design', [DesignerController::class, 'uploadDesign'])->name('tasks.uploadDesign');
    Route::post('/tasks/{card}/request-review', [DesignerController::class, 'requestReview'])->name('tasks.requestReview');
});

Route::middleware(['auth','role:designer,developer,team_lead,admin'])->prefix('member')->name('member.')->group(function () {
    Route::get('/', [MemberDashboardController::class, 'index'])->name('dashboard');
    Route::get('/cards/{card}', [MemberCardController::class, 'show'])->name('cards.show');
    
    // Subtasks index - view all my subtasks
    Route::get('/subtasks', [MemberCardController::class, 'subtasksIndex'])->name('subtasks.index');
    // Route update status dihapus - member tidak bisa ubah status card
    
    // Timer routes
    Route::post('/cards/{card}/timer/start', [MemberCardController::class, 'startTimer'])->name('cards.timer.start');
    Route::post('/cards/{card}/timer/pause', [MemberCardController::class, 'pauseTimer'])->name('cards.timer.pause');
    Route::post('/cards/{card}/timer/stop', [MemberCardController::class, 'stopTimer'])->name('cards.timer.stop');
    Route::get('/cards/{card}/timer/status', [MemberCardController::class, 'getTimerStatus'])->name('cards.timer.status');
    
    // Extension request
    Route::post('/cards/{card}/request-extension', [MemberCardController::class, 'requestExtension'])->name('cards.request-extension');
    
    // Comments
    Route::post('/cards/{card}/comment', [MemberCardController::class, 'addComment'])->name('cards.comment');
    
    // Subtasks - member can add, update, and delete subtasks
    Route::post('/cards/{card}/subtask', [MemberCardController::class, 'addSubtask'])->name('cards.subtask.add');
    Route::patch('/subtasks/{subtask}', [MemberCardController::class, 'updateSubtask'])->name('subtasks.update');
    Route::delete('/subtasks/{subtask}', [MemberCardController::class, 'deleteSubtask'])->name('subtasks.delete');
});

// Notification routes (available for all authenticated users)
Route::middleware(['auth'])->prefix('notifications')->name('notifications.')->group(function () {
    Route::get('/', [NotificationController::class, 'index'])->name('index');
    Route::get('/count', [NotificationController::class, 'getUnreadCount'])->name('count');
    Route::get('/recent', [NotificationController::class, 'getRecent'])->name('recent');
    Route::get('/{id}', [NotificationController::class, 'show'])->name('show');
    Route::post('/{id}/read', [NotificationController::class, 'markAsRead'])->name('read');
    Route::post('/read-all', [NotificationController::class, 'markAllAsRead'])->name('readAll');
    Route::delete('/{id}', [NotificationController::class, 'destroy'])->name('destroy');
});

// Root route - redirect based on auth status and role
Route::get('/', function () {
    if (!Auth::check()) {
        return redirect('/login');
    }
    
    $role = Auth::user()->role;
    
    return match($role) {
        'admin' => redirect('/admin'),
        'team_lead' => redirect('/lead'),
        'designer', 'developer' => redirect('/member'),
        default => redirect('/member'),
    };
});


