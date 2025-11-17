# Team Lead Board Creation in Assigned Projects

## Overview
Fitur ini memungkinkan team lead untuk langsung membuat board di project yang telah diberikan/ditugaskan kepadanya. Authorization sekarang berdasarkan keanggotaan di `project_members` table, bukan lagi berdasarkan `created_by` atau `assigned_to` field.

## Business Logic

### Aturan
1. Team lead yang **menjadi member** di suatu project dapat:
   - Membuat board baru di project tersebut
   - Mengubah board yang ada di project tersebut
   - Menghapus board di project tersebut
   
2. Validasi berdasarkan:
   - User harus ada di table `project_members`
   - Dengan `role = 'team_lead'`
   - Untuk `project_id` yang sesuai

3. Team lead hanya bisa mengelola board di project yang mereka ikuti sebagai member

### Skenario
- ✅ **BOLEH**: Team lead yang jadi member project bisa create/update/delete board
- ❌ **TIDAK BOLEH**: Team lead yang bukan member project tidak bisa akses board
- ✅ **BOLEH**: Satu project bisa punya multiple team leads (multiple members dengan role team_lead)

## Implementation

### Files Modified

#### 1. `app/Http/Controllers/API/BoardController.php`
Authorization sekarang mengecek `project_members` table:

**Before:**
```php
if ($user->role !== 'team_lead' || $project->assigned_to !== $user->id) {
    return response()->json([
        'success' => false,
        'message' => 'Only team lead assigned to this project can create boards'
    ], 403);
}
```

**After:**
```php
$isTeamLead = \App\Models\ProjectMember::where('project_id', $projectId)
    ->where('user_id', $user->id)
    ->where('role', 'team_lead')
    ->exists();

if (!$isTeamLead) {
    return response()->json([
        'success' => false,
        'message' => 'Only team lead member of this project can create boards'
    ], 403);
}
```

**Methods Updated:**
- `store()` - Create board validation
- `update()` - Update board validation
- `destroy()` - Delete board validation

#### 2. `app/Http/Controllers/Lead/BoardController.php`
Web interface untuk team lead dengan validasi berbasis project membership:

**index() Method:**
```php
$boards = Board::with('project')
    ->whereHas('project.members', function($q) use ($leadId) {
        $q->where('user_id', $leadId)
          ->where('role', 'team_lead');
    })
    ->orderByDesc('id')
    ->paginate(10);
```

**create() & edit() Methods:**
```php
$projects = Project::whereHas('members', function($q) use ($leadId) {
        $q->where('user_id', $leadId)
          ->where('role', 'team_lead');
    })
    ->orderBy('project_name')
    ->get();
```

**store() Method:**
```php
$isMember = \App\Models\ProjectMember::where('project_id', $data['project_id'])
    ->where('user_id', $leadId)
    ->where('role', 'team_lead')
    ->exists();
    
if (!$isMember) {
    abort(403, 'You are not a team lead member of this project');
}
```

**authorizeBoard() Method:**
```php
$isMember = \App\Models\ProjectMember::where('project_id', $board->project->id)
    ->where('user_id', $leadId)
    ->where('role', 'team_lead')
    ->exists();
    
if (!$isMember) {
    abort(403);
}
```

## Database Structure

### Relevant Tables

#### projects
```sql
CREATE TABLE projects (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    project_name VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    created_by BIGINT UNSIGNED NULL,      -- Admin yang create
    assigned_to BIGINT UNSIGNED NULL,     -- (Legacy, tidak digunakan lagi)
    deadline DATE NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

#### project_members (Main Authorization Source)
```sql
CREATE TABLE project_members (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    project_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    role ENUM('team_lead','developer','designer') NOT NULL,
    joined_at TIMESTAMP NOT NULL
);
```

#### boards
```sql
CREATE TABLE boards (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    project_id BIGINT UNSIGNED NOT NULL,
    board_name VARCHAR(150) NOT NULL,
    description TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

## API Endpoints

### 1. POST /api/projects/{projectId}/boards
**Create new board in project**

**Authorization:** Bearer Token (Team Lead Member)

**Request:**
```json
{
    "board_name": "Sprint 1",
    "description": "First sprint for MVP development"
}
```

**Success Response (201):**
```json
{
    "success": true,
    "message": "Board created successfully",
    "data": {
        "id": 1,
        "project_id": 5,
        "board_name": "Sprint 1",
        "description": "First sprint for MVP development",
        "created_at": "2025-11-13T10:00:00.000000Z",
        "updated_at": "2025-11-13T10:00:00.000000Z"
    }
}
```

**Error Response (403) - Not a Team Lead Member:**
```json
{
    "success": false,
    "message": "Only team lead member of this project can create boards"
}
```

### 2. GET /api/projects/{projectId}/boards
**Get all boards in project**

**Success Response (200):**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "project_id": 5,
            "board_name": "Sprint 1",
            "description": "First sprint",
            "cards": []
        },
        {
            "id": 2,
            "project_id": 5,
            "board_name": "Sprint 2",
            "description": "Second sprint",
            "cards": []
        }
    ]
}
```

### 3. PUT /api/projects/{projectId}/boards/{boardId}
**Update existing board**

**Request:**
```json
{
    "board_name": "Sprint 1 - Updated",
    "description": "Updated description"
}
```

**Success Response (200):**
```json
{
    "success": true,
    "message": "Board updated successfully",
    "data": { ... }
}
```

### 4. DELETE /api/projects/{projectId}/boards/{boardId}
**Delete board**

**Success Response (200):**
```json
{
    "success": true,
    "message": "Board deleted successfully"
}
```

## Web Routes (Team Lead Interface)

### GET /lead/boards
Display all boards from projects where user is team lead member

### GET /lead/boards/create
Show form to create new board (dropdown hanya project yang user jadi team lead)

### POST /lead/boards
Create new board with validation

### GET /lead/boards/{board}/edit
Show form to edit board

### PUT /lead/boards/{board}
Update board

### DELETE /lead/boards/{board}
Delete board

## Testing

### 1. Setup Test Data

**Create Project:**
```sql
INSERT INTO projects (id, project_name, description, created_by, deadline, created_at, updated_at)
VALUES (10, 'E-Commerce Platform', 'Build online store', 1, '2025-12-31', NOW(), NOW());
```

**Add Team Lead as Member:**
```sql
INSERT INTO project_members (project_id, user_id, role, joined_at)
VALUES (10, 5, 'team_lead', NOW());
-- User 5 is now team lead member of project 10
```

**Add Other Members:**
```sql
INSERT INTO project_members (project_id, user_id, role, joined_at)
VALUES 
    (10, 6, 'developer', NOW()),
    (10, 7, 'designer', NOW());
```

### 2. Test API - Create Board as Team Lead

**Login as Team Lead (user 5):**
```bash
curl -X POST http://127.0.0.1/UKK/public/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "username": "teamlead_user",
    "password": "password"
  }'
```

**Create Board:**
```bash
curl -X POST http://127.0.0.1/UKK/public/api/projects/10/boards \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "board_name": "Development Sprint 1",
    "description": "Initial development sprint"
  }'
```

**Expected Result:**
```json
{
    "success": true,
    "message": "Board created successfully",
    "data": {
        "id": 15,
        "project_id": 10,
        "board_name": "Development Sprint 1",
        "description": "Initial development sprint",
        "created_at": "2025-11-13T10:30:00.000000Z",
        "updated_at": "2025-11-13T10:30:00.000000Z"
    }
}
```

### 3. Test API - Non-Member Cannot Create Board

**Login as Different User (user 8 - not a member):**
```bash
curl -X POST http://127.0.0.1/UKK/public/api/projects/10/boards \
  -H "Authorization: Bearer OTHER_USER_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "board_name": "Unauthorized Board",
    "description": "This should fail"
  }'
```

**Expected Result:**
```json
{
    "success": false,
    "message": "Only team lead member of this project can create boards"
}
```

### 4. Verify Database State

**Check Team Lead's Projects:**
```sql
SELECT 
    p.id,
    p.project_name,
    pm.user_id,
    u.username,
    pm.role
FROM projects p
JOIN project_members pm ON p.id = pm.project_id
JOIN users u ON pm.user_id = u.id
WHERE pm.role = 'team_lead';
```

**Check Boards Created:**
```sql
SELECT 
    b.id,
    b.board_name,
    p.project_name,
    pm.user_id as team_lead_id,
    u.username as team_lead_name
FROM boards b
JOIN projects p ON b.project_id = p.id
JOIN project_members pm ON p.id = pm.project_id AND pm.role = 'team_lead'
JOIN users u ON pm.user_id = u.id;
```

**Verify Team Lead Can Create Board:**
```sql
-- Check if user 5 is team lead of project 10
SELECT EXISTS(
    SELECT 1 
    FROM project_members 
    WHERE project_id = 10 
      AND user_id = 5 
      AND role = 'team_lead'
) as can_create_board;
-- Returns 1 (true) if allowed, 0 (false) if not
```

## Migration from Old System

### Old System
- Authorization based on `projects.assigned_to` field
- Single team lead per project
- Field: `assigned_to BIGINT UNSIGNED`

### New System
- Authorization based on `project_members` table
- Multiple team leads per project (if needed)
- Flexible role-based membership

### Migration Steps (if needed)

**Migrate existing assignments to project_members:**
```sql
-- Copy assigned_to to project_members as team_lead
INSERT INTO project_members (project_id, user_id, role, joined_at)
SELECT 
    id as project_id,
    assigned_to as user_id,
    'team_lead' as role,
    created_at as joined_at
FROM projects
WHERE assigned_to IS NOT NULL
  AND NOT EXISTS (
      SELECT 1 FROM project_members pm 
      WHERE pm.project_id = projects.id 
        AND pm.user_id = projects.assigned_to
  );
```

## Benefits

1. ✅ **Flexible Membership**: Multiple team leads per project
2. ✅ **Consistent Authorization**: Semua role menggunakan project_members table
3. ✅ **Better Scalability**: Mudah menambah/mengurangi team members
4. ✅ **Clear Separation**: Owner (created_by) vs Team Lead Member (project_members)
5. ✅ **Audit Trail**: joined_at timestamp untuk tracking

## Related Features
- Project Member Management
- Role-based Access Control  
- Card Assignment System
- Project Member Task Restriction

## Notes
- Team lead hanya bisa manage board di project yang mereka ikuti
- Satu project bisa punya multiple team leads
- Developer dan Designer tidak bisa create/update/delete boards
- Admin memiliki akses penuh via different routes

---

**Created:** November 13, 2025  
**Version:** 1.0
