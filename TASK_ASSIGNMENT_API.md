# Task Assignment System - API Documentation

## Overview
Sistem ini memungkinkan **Team Lead** untuk membuat dan assign task ke **Member** (Designer/Developer). Member hanya bisa melihat dan mengerjakan task yang sudah di-assign ke mereka.

---

## Roles & Permissions

### 🔵 Team Lead (`team_lead`)
- ✅ Create new tasks
- ✅ Assign tasks to members
- ✅ View all tasks in their projects
- ✅ Update any task
- ✅ Delete tasks

### 🟢 Member (`designer` / `developer`)
- ✅ View only their assigned tasks
- ✅ Update status of their tasks (todo → in_progress → done)
- ✅ Add comments to their tasks
- ❌ Cannot create new tasks
- ❌ Cannot view other members' tasks

---

## Sample Users

Jalankan seeder: `php artisan db:seed --class=TaskAssignmentSeeder`

```
Team Lead:   teamlead / password
Designer:    desainer / password
Developer:   developer / password
```

---

## API Endpoints

### 1. Login

**POST** `/api/login`

```json
{
  "login": "desainer",
  "password": "password"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": {
      "id": 1,
      "username": "desainer",
      "fullname": "UI/UX Designer",
      "role": "designer"
    },
    "token": "1|abc123..."
  }
}
```

---

### 2. Get My Assigned Tasks (Member Only)

**GET** `/api/my-tasks`

**Headers:**
```
Authorization: Bearer {token}
```

**Query Parameters:**
- `status` (optional): `todo`, `in_progress`, `done`
- `priority` (optional): `low`, `medium`, `high`

**Example:**
```
GET /api/my-tasks?status=in_progress
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "card_title": "Create Homepage Wireframe",
      "description": "Design wireframe for homepage...",
      "status": "in_progress",
      "priority": "high",
      "due_date": "2025-12-01",
      "estimated_hours": "8.00",
      "actual_hours": null,
      "board": {
        "id": 1,
        "board_name": "Design Tasks",
        "project": {
          "id": 1,
          "project_name": "E-Commerce Website"
        }
      },
      "creator": {
        "id": 3,
        "fullname": "Team Lead"
      },
      "subtasks": [
        {
          "id": 1,
          "subtask_title": "Hero section design",
          "status": "done"
        }
      ]
    }
  ],
  "meta": {
    "total": 4,
    "todo": 3,
    "in_progress": 1,
    "done": 0
  }
}
```

---

### 3. Get Task Detail

**GET** `/api/cards/{id}`

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "card_title": "Create Homepage Wireframe",
    "description": "Design wireframe for homepage...",
    "status": "in_progress",
    "priority": "high",
    "due_date": "2025-12-01",
    "board": { ... },
    "assignedTo": {
      "id": 1,
      "fullname": "UI/UX Designer"
    },
    "creator": {
      "id": 3,
      "fullname": "Team Lead"
    },
    "subtasks": [...],
    "comments": [...]
  }
}
```

---

### 4. Update Task Status (Member)

**PUT** `/api/cards/{id}/status`

**Body:**
```json
{
  "status": "in_progress"
}
```

**Allowed Status Values:**
- `todo`
- `in_progress`
- `done`

**Response:**
```json
{
  "success": true,
  "message": "Task status updated successfully",
  "data": { ... }
}
```

---

### 5. Create New Task (Team Lead Only)

**POST** `/api/boards/{board_id}/cards`

**Body:**
```json
{
  "card_title": "Design Login Page",
  "description": "Create modern login page with social auth",
  "priority": "high",
  "status": "todo",
  "estimated_hours": 6,
  "due_date": "2025-12-15",
  "assigned_to": 1
}
```

**Note:** `assigned_to` adalah ID user (member) yang akan dikerjakan task ini.

**Response:**
```json
{
  "success": true,
  "message": "Task created and assigned successfully",
  "data": { ... }
}
```

---

### 6. Update Task (Role-Based)

**PUT** `/api/boards/{board_id}/cards/{card_id}`

**For Members:**
- Hanya bisa update task yang di-assign ke mereka
- Hanya bisa update `status` dan `actual_hours`

**Body (Member):**
```json
{
  "status": "done",
  "actual_hours": 7.5
}
```

**For Team Lead:**
- Bisa update semua field termasuk assign ke member lain

**Body (Team Lead):**
```json
{
  "card_title": "Updated Title",
  "description": "Updated description",
  "priority": "high",
  "status": "in_progress",
  "estimated_hours": 10,
  "assigned_to": 2
}
```

---

### 7. Add Comment

**POST** `/api/cards/{card_id}/comments`

**Body:**
```json
{
  "comment": "I've finished the hero section design"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Comment added successfully",
  "data": {
    "id": 1,
    "comment": "I've finished the hero section design",
    "user": {
      "id": 1,
      "fullname": "UI/UX Designer"
    },
    "created_at": "2025-11-06T10:30:00.000000Z"
  }
}
```

---

### 8. Get Dashboard Stats

**GET** `/api/dashboard`

**For Members:**
```json
{
  "success": true,
  "data": {
    "assigned_tasks": 4,
    "todo": 3,
    "in_progress": 1,
    "done": 0,
    "high_priority": 2
  }
}
```

**For Team Lead:**
```json
{
  "success": true,
  "data": {
    "my_projects": 1,
    "my_boards": 2,
    "total_cards": 9,
    "cards_by_status": [...]
  }
}
```

---

## Testing Examples

### Test as Designer

```bash
# 1. Login
POST http://localhost:8001/api/login
{
  "login": "desainer",
  "password": "password"
}

# 2. Get my tasks
GET http://localhost:8001/api/my-tasks
Authorization: Bearer {token}

# 3. Update task status
PUT http://localhost:8001/api/cards/1/status
Authorization: Bearer {token}
{
  "status": "in_progress"
}

# 4. Add comment
POST http://localhost:8001/api/cards/1/comments
Authorization: Bearer {token}
{
  "comment": "Working on this now"
}
```

### Test as Team Lead

```bash
# 1. Login
POST http://localhost:8001/api/login
{
  "login": "teamlead",
  "password": "password"
}

# 2. Create new task
POST http://localhost:8001/api/boards/1/cards
Authorization: Bearer {token}
{
  "card_title": "New Design Task",
  "description": "Create new page design",
  "priority": "medium",
  "estimated_hours": 8,
  "due_date": "2025-12-20",
  "assigned_to": 1
}

# 3. View all tasks in board
GET http://localhost:8001/api/boards/1/cards
Authorization: Bearer {token}
```

---

## Error Responses

### 403 Forbidden (Member tries to access others' task)
```json
{
  "success": false,
  "message": "You can only view your own assigned tasks"
}
```

### 403 Forbidden (Member tries to create task)
```json
{
  "success": false,
  "message": "Only team lead can create tasks"
}
```

### 403 Forbidden (Member tries to update others' task)
```json
{
  "success": false,
  "message": "You can only update your own assigned tasks"
}
```

---

## Database Structure

### management_project_cards

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| board_id | bigint | FK to boards |
| card_title | string | Task title |
| description | text | Task description |
| created_by | bigint | FK to users (team lead) |
| **assigned_to** | bigint | **FK to users (member)** |
| status | enum | todo, in_progress, done |
| priority | enum | low, medium, high |
| due_date | date | Deadline |
| estimated_hours | decimal | Estimated time |
| actual_hours | decimal | Actual time spent |

---

## Key Features

✅ Role-based access control
✅ Team lead dapat assign task ke member
✅ Member hanya melihat task mereka sendiri
✅ Member dapat update status dan actual hours
✅ Auto-filtering based on role
✅ Dashboard statistics per role
✅ Complete audit trail (creator & assignee)

---

## Migration & Seeding

```bash
# Run migration
php artisan migrate

# Seed sample data
php artisan db:seed --class=TaskAssignmentSeeder

# Fresh migration + seed
php artisan migrate:fresh --seed --seeder=TaskAssignmentSeeder
```
