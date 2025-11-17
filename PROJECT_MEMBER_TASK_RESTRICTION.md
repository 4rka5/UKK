# Project Member Task Restriction Feature

## Overview
Fitur ini memastikan bahwa user yang sudah bergabung sebagai member di suatu project **tidak dapat diberikan tugas (task/card assignment)**. Ini mencegah konflik assignment dan memastikan pemisahan yang jelas antara:
- **Project Members**: User yang bergabung sebagai team member dengan role (team_lead, developer, designer)
- **Task Assignees**: User freelance/eksternal yang diberikan task tertentu tanpa menjadi full member

## Business Logic

### Aturan
1. User yang sudah tercatat di `project_members` **tidak boleh** di-assign ke task (card)
2. User yang belum menjadi project member **boleh** di-assign ke task individual
3. Validasi dilakukan saat:
   - Membuat task baru dengan assignment (`store`)
   - Mengubah assignment task yang ada (`update`)
   - Menambahkan user ke card (`assignUser`)

### Skenario
- ✅ **BOLEH**: Assign task ke user freelance yang tidak ada di project_members
- ❌ **TIDAK BOLEH**: Assign task ke user yang sudah jadi developer/designer di project
- ✅ **BOLEH**: Member project mengerjakan task tanpa perlu assignment (akses langsung via role)

## Implementation

### Files Modified
1. `app/Http/Controllers/API/CardController.php`
   - Modified `store()` method
   - Modified `update()` method  
   - Modified `assignUser()` method

### Code Changes

#### 1. Store Method (Create Task with Assignment)
```php
// Check if assigned user is already a project member
if ($request->assigned_to) {
    $board = \App\Models\ManagementProjectBoard::findOrFail($boardId);
    $project = $board->project;
    
    $isProjectMember = \App\Models\ProjectMember::where('project_id', $project->id)
        ->where('user_id', $request->assigned_to)
        ->exists();

    if ($isProjectMember) {
        $assignedUser = \App\Models\User::find($request->assigned_to);
        return response()->json([
            'success' => false,
            'message' => "User {$assignedUser->username} sudah bergabung di project {$project->project_name}. User yang sudah bergabung di project tidak dapat diberikan tugas."
        ], 422);
    }
}
```

#### 2. Update Method (Change Task Assignment)
```php
// Check if assigned user is already a project member (when updating assignment)
if ($request->has('assigned_to') && $request->assigned_to != $card->assigned_to) {
    $board = $card->board;
    $project = $board->project;
    
    $isProjectMember = \App\Models\ProjectMember::where('project_id', $project->id)
        ->where('user_id', $request->assigned_to)
        ->exists();

    if ($isProjectMember) {
        $assignedUser = \App\Models\User::find($request->assigned_to);
        return response()->json([
            'success' => false,
            'message' => "User {$assignedUser->username} sudah bergabung di project {$project->project_name}. User yang sudah bergabung di project tidak dapat diberikan tugas."
        ], 422);
    }
}
```

#### 3. AssignUser Method (Add User to Card)
```php
// Check if user is already a project member
$board = $card->board;
$project = $board->project;

$isProjectMember = \App\Models\ProjectMember::where('project_id', $project->id)
    ->where('user_id', $request->user_id)
    ->exists();

if ($isProjectMember) {
    $user = \App\Models\User::find($request->user_id);
    return response()->json([
        'success' => false,
        'message' => "User {$user->username} sudah bergabung di project {$project->project_name}. User yang sudah bergabung di project tidak dapat diberikan tugas."
    ], 422);
}
```

## Database Structure

### Relevant Tables

#### project_members
```sql
CREATE TABLE project_members (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    project_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    role ENUM('team_lead','developer','designer') NOT NULL,
    joined_at TIMESTAMP NOT NULL
);
```

#### card_assignments
```sql
CREATE TABLE card_assignments (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    card_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    assignment_status ENUM('assigned','in_progress','completed') DEFAULT 'assigned',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

## API Endpoints Affected

### 1. POST /api/boards/{boardId}/cards
**Create new task with assignment**

**Request:**
```json
{
    "card_title": "Design Landing Page",
    "description": "Create modern landing page",
    "assigned_to": 5,  // User ID
    "priority": "high",
    "due_date": "2025-11-20"
}
```

**Success Response (200):**
```json
{
    "success": true,
    "message": "Task created and assigned successfully",
    "data": { ... }
}
```

**Error Response (422) - User is Project Member:**
```json
{
    "success": false,
    "message": "User john_doe sudah bergabung di project Website Redesign. User yang sudah bergabung di project tidak dapat diberikan tugas."
}
```

### 2. PUT /api/boards/{boardId}/cards/{cardId}
**Update task assignment**

**Request:**
```json
{
    "assigned_to": 7  // Change assignee
}
```

**Error Response (422) - New User is Project Member:**
```json
{
    "success": false,
    "message": "User jane_smith sudah bergabung di project Mobile App. User yang sudah bergabung di project tidak dapat diberikan tugas."
}
```

### 3. POST /api/cards/{cardId}/assign
**Assign user to existing card**

**Request:**
```json
{
    "user_id": 8
}
```

**Error Response (422):**
```json
{
    "success": false,
    "message": "User mike_dev sudah bergabung di project E-Commerce Platform. User yang sudah bergabung di project tidak dapat diberikan tugas."
}
```

## Testing

### 1. Test Project Member Cannot Be Assigned

**Setup:**
```sql
-- Create project
INSERT INTO projects (id, project_name, created_by) VALUES (1, 'Test Project', 1);

-- Add user as project member
INSERT INTO project_members (project_id, user_id, role, joined_at) 
VALUES (1, 5, 'developer', NOW());

-- Create board
INSERT INTO boards (id, project_id, board_name) VALUES (1, 1, 'Sprint 1');
```

**Test API Request:**
```bash
curl -X POST http://127.0.0.1/UKK/public/api/boards/1/cards \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "card_title": "Test Task",
    "assigned_to": 5
  }'
```

**Expected Result:**
```json
{
    "success": false,
    "message": "User username sudah bergabung di project Test Project. User yang sudah bergabung di project tidak dapat diberikan tugas."
}
```

### 2. Test Non-Member Can Be Assigned

**Setup:**
```sql
-- User 10 is NOT a project member
SELECT * FROM project_members WHERE user_id = 10 AND project_id = 1;
-- Returns empty (no rows)
```

**Test API Request:**
```bash
curl -X POST http://127.0.0.1/UKK/public/api/boards/1/cards \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "card_title": "Freelance Task",
    "assigned_to": 10
  }'
```

**Expected Result:**
```json
{
    "success": true,
    "message": "Task created and assigned successfully",
    "data": { ... }
}
```

### 3. Verify Database State

**Check who can be assigned:**
```sql
-- Get users who are NOT members of project 1
SELECT u.id, u.username, u.full_name
FROM users u
WHERE u.id NOT IN (
    SELECT user_id 
    FROM project_members 
    WHERE project_id = 1
)
AND u.role IN ('designer', 'developer');
```

**Check current project members:**
```sql
SELECT 
    pm.id,
    u.username,
    pm.role,
    p.project_name,
    pm.joined_at
FROM project_members pm
JOIN users u ON pm.user_id = u.id
JOIN projects p ON pm.project_id = p.id
WHERE p.id = 1;
```

## Error Handling

### Validation Flow
1. Check if `assigned_to` field is provided
2. Retrieve board and project information
3. Query `project_members` table for matching user_id + project_id
4. If exists → Return 422 error with descriptive message
5. If not exists → Proceed with assignment

### Error Message Format
```
User {username} sudah bergabung di project {project_name}. User yang sudah bergabung di project tidak dapat diberikan tugas.
```

## Benefits
1. ✅ **Prevents Conflicts**: Menghindari user memiliki dual role (member + assignee)
2. ✅ **Clear Separation**: Membedakan team member vs freelance/external workers
3. ✅ **Data Integrity**: Memastikan konsistensi assignment logic
4. ✅ **Better Tracking**: Memudahkan tracking siapa yang full member vs task-based contributor

## Related Features
- Auto Status Update (when user becomes project owner)
- Role-based Access Control
- Project Member Management
- Card Assignment System

## Notes
- Validasi hanya berlaku untuk **assignment baru** atau **perubahan assignment**
- Member yang sudah ada tetap bisa mengerjakan task via role access
- Team lead tetap bisa mengelola semua task di project mereka
- Admin memiliki akses penuh ke semua project

---

**Created:** November 13, 2025  
**Modified:** November 13, 2025  
**Version:** 1.0
