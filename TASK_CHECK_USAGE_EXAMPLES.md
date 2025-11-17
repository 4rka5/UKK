# Task Check Logic - Usage Examples

## Overview
Logika untuk mengecek apakah user sudah memiliki tugas atau belum berdasarkan role mereka.

## Business Rules

### Admin
- **Tidak perlu dicek** - Admin tidak memiliki tugas yang di-assign
- Method `hasTasks()` akan selalu return `true`

### Team Lead
- **Cek dari tabel `projects`** berdasarkan kolom `assigned_to`
- Team lead yang memiliki project yang di-assign dianggap sudah punya tugas

### Members (Designer/Developer)
- **Cek dari tabel `card_assignments`** berdasarkan `user_id`
- Member yang memiliki card yang di-assign dianggap sudah punya tugas

## Methods Available in User Model

### 1. `hasTasks()` - Boolean Check
Mengecek apakah user sudah memiliki tugas atau belum.

```php
$user = Auth::user();

if ($user->hasTasks()) {
    return response()->json([
        'message' => 'User already has assigned tasks',
        'has_tasks' => true
    ]);
} else {
    return response()->json([
        'message' => 'User has no tasks assigned',
        'has_tasks' => false
    ]);
}
```

### 2. `getTasksCount()` - Integer Count
Mendapatkan jumlah tugas yang dimiliki user.

```php
$user = Auth::user();
$taskCount = $user->getTasksCount();

return response()->json([
    'user' => $user->fullname,
    'role' => $user->role,
    'tasks_count' => $taskCount
]);
```

### 3. `getTasks()` - Collection of Tasks
Mendapatkan semua tugas yang dimiliki user.

```php
$user = Auth::user();
$tasks = $user->getTasks();

if ($user->isLead()) {
    // $tasks berisi collection of Projects
    return response()->json([
        'projects' => $tasks
    ]);
} else {
    // $tasks berisi collection of Cards
    return response()->json([
        'cards' => $tasks
    ]);
}
```

## Controller Example

### Check Task Status Endpoint

```php
// In routes/api.php
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user/task-status', [UserController::class, 'checkTaskStatus']);
});

// In app/Http/Controllers/UserController.php
public function checkTaskStatus(Request $request)
{
    $user = $request->user();
    
    // Skip check for admin
    if ($user->isAdmin()) {
        return response()->json([
            'message' => 'Admin role does not require task check',
            'role' => 'admin',
            'has_tasks' => true
        ]);
    }
    
    $hasTasks = $user->hasTasks();
    $tasksCount = $user->getTasksCount();
    $tasks = $user->getTasks();
    
    return response()->json([
        'user_id' => $user->id,
        'username' => $user->username,
        'role' => $user->role,
        'has_tasks' => $hasTasks,
        'tasks_count' => $tasksCount,
        'tasks' => $tasks
    ]);
}
```

### Dashboard with Task Check

```php
public function dashboard(Request $request)
{
    $user = $request->user();
    
    if ($user->isAdmin()) {
        // Admin dashboard
        return response()->json([
            'role' => 'admin',
            'message' => 'Welcome Admin'
        ]);
    }
    
    if ($user->isLead()) {
        // Team Lead dashboard
        $projects = $user->getTasks(); // Get assigned projects
        
        return response()->json([
            'role' => 'team_lead',
            'has_tasks' => $user->hasTasks(),
            'projects_count' => $projects->count(),
            'projects' => $projects
        ]);
    }
    
    // Member dashboard (Designer/Developer)
    $cards = $user->getTasks(); // Get assigned cards
    
    return response()->json([
        'role' => $user->role,
        'has_tasks' => $user->hasTasks(),
        'cards_count' => $cards->count(),
        'cards' => $cards
    ]);
}
```

### Middleware Example - Prevent Access Without Tasks

```php
// app/Http/Middleware/EnsureUserHasTasks.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureUserHasTasks
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        
        // Skip check for admin
        if ($user->isAdmin()) {
            return $next($request);
        }
        
        if (!$user->hasTasks()) {
            return response()->json([
                'message' => 'You need to have tasks assigned to access this resource',
                'has_tasks' => false
            ], 403);
        }
        
        return $next($request);
    }
}
```

## Query Examples

### For Team Lead
```php
// Check if team lead has projects
$teamLead = User::find($userId);
if ($teamLead->isLead() && $teamLead->hasTasks()) {
    $projects = Project::where('assigned_to', $teamLead->id)->get();
}
```

### For Members (Designer/Developer)
```php
// Check if member has cards
$member = User::find($userId);
if (!$member->isAdmin() && !$member->isLead() && $member->hasTasks()) {
    $cards = $member->assignedCards; // Using relationship
}
```

### Batch Check for Multiple Users
```php
// Get all users without tasks (excluding admin)
$usersWithoutTasks = User::where('role', '!=', 'admin')
    ->get()
    ->filter(function ($user) {
        return !$user->hasTasks();
    });

// Get task statistics by role
$statistics = [
    'team_leads_with_tasks' => User::where('role', 'team_lead')
        ->get()
        ->filter->hasTasks()
        ->count(),
    'designers_with_tasks' => User::where('role', 'designer')
        ->get()
        ->filter->hasTasks()
        ->count(),
    'developers_with_tasks' => User::where('role', 'developer')
        ->get()
        ->filter->hasTasks()
        ->count(),
];
```

## Testing Examples

```php
// Test for Team Lead
$teamLead = User::factory()->create(['role' => 'team_lead']);
$this->assertFalse($teamLead->hasTasks()); // No tasks yet

Project::factory()->create(['assigned_to' => $teamLead->id]);
$this->assertTrue($teamLead->hasTasks()); // Now has task

// Test for Designer
$designer = User::factory()->create(['role' => 'designer']);
$this->assertFalse($designer->hasTasks()); // No tasks yet

$card = ManagementProjectCard::factory()->create();
$designer->assignedCards()->attach($card->id);
$this->assertTrue($designer->hasTasks()); // Now has task

// Test for Admin
$admin = User::factory()->create(['role' => 'admin']);
$this->assertTrue($admin->hasTasks()); // Always true
```

## Notes

1. **Performance**: Methods menggunakan `exists()` untuk check boolean, lebih efisien dari `count() > 0`
2. **Relationships**: Menggunakan existing relationships dari model User
3. **Scalability**: Logic terpusat di User model, mudah untuk di-maintain
4. **Flexibility**: Bisa ditambahkan logic tambahan seperti status filtering jika diperlukan

## Future Enhancements

Jika diperlukan filter tambahan (misalnya hanya tugas yang active):

```php
public function hasActiveTasks(): bool
{
    if ($this->isAdmin()) {
        return true;
    }

    if ($this->isLead()) {
        return Project::where('assigned_to', $this->id)
            ->where('status', '!=', 'completed')
            ->exists();
    }

    return $this->assignedCards()
        ->where('status', '!=', 'completed')
        ->exists();
}
```
