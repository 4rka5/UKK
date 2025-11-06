# Project Management System - Complete Hierarchy

## 🎯 System Overview

Sistem Project Management dengan hierarki lengkap:
1. **Admin** → Creates Projects → Assigns to Team Lead
2. **Team Lead** → Creates Boards & Cards → Assigns to Members  
3. **Members** (Designer/Developer) → Work on assigned tasks

---

## 👥 User Roles & Permissions

### 1. 🔴 Admin
**Can:**
- ✅ Create Projects
- ✅ Assign Projects to Team Lead
- ✅ Update/Delete their own Projects
- ✅ View all Projects they created
- ❌ Cannot create Boards or Cards

**Dashboard Stats:**
- Total Projects Created
- Assigned Projects (with Team Lead)
- Unassigned Projects
- Total Team Leads

### 2. 🟠 Team Lead
**Can:**
- ✅ View Projects assigned to them by Admin
- ✅ Create Boards in assigned Projects
- ✅ Create Cards in their Boards
- ✅ Assign Cards to Members (Designer/Developer)
- ✅ Update/Delete Boards and Cards in their Projects
- ❌ Cannot create Projects
- ❌ Cannot access other Team Leads' Projects

**Dashboard Stats:**
- Assigned Projects (from Admin)
- Total Boards Created
- Total Cards Created  
- Assigned Cards (to Members)

### 3. 🟢 Member (Designer / Developer)
**Can:**
- ✅ View Tasks assigned to them
- ✅ Update Task status (todo → in_progress → done)
- ✅ Update actual_hours for their tasks
- ✅ Add comments to their tasks
- ❌ Cannot create Projects, Boards, or Cards
- ❌ Cannot view other Members' tasks

**Dashboard Stats:**
- Total Assigned Tasks
- Tasks by Status (todo, in_progress, done)
- High Priority Tasks

---

## 📋 Sample Users

```bash
Admin:      admin / password
Team Lead:  teamlead / password
Designer:   desainer / password
Developer:  developer / password
```

---

## 🔗 Complete API Endpoints

### Authentication

#### Login
```http
POST /api/login
Content-Type: application/json

{
  "login": "admin",
  "password": "password"
}
```

---

### Admin Endpoints

#### 1. Create Project (Admin Only)
```http
POST /api/projects
Authorization: Bearer {admin_token}

{
  "project_name": "Mobile App Development",
  "description": "Build iOS and Android app",
  "deadline": "2026-03-01",
  "assigned_to": 2
}
```
**Note:** `assigned_to` adalah ID Team Lead

#### 2. View My Projects (Admin)
```http
GET /api/projects
Authorization: Bearer {admin_token}
```

Returns: Semua project yang dibuat oleh admin

#### 3. Update Project
```http
PUT /api/projects/{id}
Authorization: Bearer {admin_token}

{
  "project_name": "Updated Name",
  "assigned_to": 3
}
```

#### 4. Delete Project
```http
DELETE /api/projects/{id}
Authorization: Bearer {admin_token}
```

---

### Team Lead Endpoints

#### 1. View Assigned Projects
```http
GET /api/projects
Authorization: Bearer {teamlead_token}
```

Returns: Project yang di-assign ke team lead ini

#### 2. Create Board (Team Lead Only)
```http
POST /api/projects/{project_id}/boards
Authorization: Bearer {teamlead_token}

{
  "board_name": "Backend Development",
  "description": "API and Database tasks"
}
```

**Permission Check:** Hanya team lead yang assigned ke project ini

#### 3. View Boards in Project
```http
GET /api/projects/{project_id}/boards
Authorization: Bearer {teamlead_token}
```

#### 4. Create Card (Team Lead Only)
```http
POST /api/boards/{board_id}/cards
Authorization: Bearer {teamlead_token}

{
  "card_title": "Build User Authentication API",
  "description": "Implement login, register, logout",
  "priority": "high",
  "status": "todo",
  "estimated_hours": 12,
  "due_date": "2025-12-20",
  "assigned_to": 4
}
```

**Note:** `assigned_to` adalah ID Member (Designer/Developer)

#### 5. View Cards in Board
```http
GET /api/boards/{board_id}/cards
Authorization: Bearer {teamlead_token}
```

#### 6. Update Card
```http
PUT /api/boards/{board_id}/cards/{card_id}
Authorization: Bearer {teamlead_token}

{
  "card_title": "Updated Title",
  "assigned_to": 5,
  "priority": "medium"
}
```

---

### Member Endpoints

#### 1. View My Assigned Tasks
```http
GET /api/my-tasks
Authorization: Bearer {member_token}

# With filters:
GET /api/my-tasks?status=in_progress
GET /api/my-tasks?priority=high
```

#### 2. Get Task Detail
```http
GET /api/cards/{card_id}
Authorization: Bearer {member_token}
```

#### 3. Update Task Status
```http
PUT /api/cards/{card_id}/status
Authorization: Bearer {member_token}

{
  "status": "in_progress"
}
```

**Allowed:** `todo`, `in_progress`, `done`

#### 4. Update Task (Member - Limited)
```http
PUT /api/boards/{board_id}/cards/{card_id}
Authorization: Bearer {member_token}

{
  "status": "done",
  "actual_hours": 10.5
}
```

**Note:** Member hanya bisa update `status` dan `actual_hours`

#### 5. Add Comment
```http
POST /api/cards/{card_id}/comments
Authorization: Bearer {member_token}

{
  "comment": "Finished the API implementation"
}
```

---

### Dashboard Stats

#### Get Dashboard (All Roles)
```http
GET /api/dashboard
Authorization: Bearer {token}
```

**Admin Response:**
```json
{
  "success": true,
  "data": {
    "total_projects": 5,
    "assigned_projects": 3,
    "unassigned_projects": 2,
    "total_team_leads": 2
  }
}
```

**Team Lead Response:**
```json
{
  "success": true,
  "data": {
    "assigned_projects": 2,
    "total_boards": 4,
    "total_cards": 15,
    "assigned_cards": 12
  }
}
```

**Member Response:**
```json
{
  "success": true,
  "data": {
    "assigned_tasks": 8,
    "todo": 3,
    "in_progress": 2,
    "done": 3,
    "high_priority": 4
  }
}
```

---

## 🔄 Complete Workflow

### 1. Admin Creates Project
```http
POST /api/login
{
  "login": "admin",
  "password": "password"
}

POST /api/projects
Authorization: Bearer {admin_token}
{
  "project_name": "E-Commerce Platform",
  "description": "Full stack e-commerce",
  "deadline": "2026-06-01",
  "assigned_to": 2
}
```

### 2. Team Lead Creates Board & Cards
```http
POST /api/login
{
  "login": "teamlead",
  "password": "password"
}

POST /api/projects/1/boards
Authorization: Bearer {teamlead_token}
{
  "board_name": "Design Tasks",
  "description": "UI/UX Design"
}

POST /api/boards/1/cards
Authorization: Bearer {teamlead_token}
{
  "card_title": "Homepage Design",
  "description": "Create homepage mockup",
  "priority": "high",
  "estimated_hours": 8,
  "due_date": "2025-12-15",
  "assigned_to": 3
}
```

### 3. Member Works on Task
```http
POST /api/login
{
  "login": "desainer",
  "password": "password"
}

GET /api/my-tasks
Authorization: Bearer {designer_token}

PUT /api/cards/1/status
Authorization: Bearer {designer_token}
{
  "status": "in_progress"
}

POST /api/cards/1/comments
Authorization: Bearer {designer_token}
{
  "comment": "Started working on the hero section"
}

PUT /api/cards/1/status
Authorization: Bearer {designer_token}
{
  "status": "done"
}

PUT /api/boards/1/cards/1
Authorization: Bearer {designer_token}
{
  "actual_hours": 7.5
}
```

---

## 🚫 Permission Errors

### Admin tries to create Board
```json
{
  "success": false,
  "message": "Only team lead assigned to this project can create boards"
}
```

### Member tries to create Card
```json
{
  "success": false,
  "message": "Only team lead can create tasks"
}
```

### Member tries to view other's task
```json
{
  "success": false,
  "message": "You can only view your own assigned tasks"
}
```

### Team Lead tries to create Project
```json
{
  "success": false,
  "message": "Only admin can create projects"
}
```

---

## 📊 Database Structure

### projects
- `id`
- `project_name`
- `description`
- `deadline`
- `created_by` (FK to users - Admin)
- **`assigned_to`** (FK to users - Team Lead)

### boards
- `id`
- `project_id` (FK to projects)
- `board_name`
- `description`

### cards
- `id`
- `board_id` (FK to boards)
- `card_title`
- `description`
- `created_by` (FK to users - Team Lead)
- **`assigned_to`** (FK to users - Member)
- `status` (todo, in_progress, done)
- `priority` (low, medium, high)
- `due_date`
- `estimated_hours`
- `actual_hours`

---

## ⚙️ Setup & Installation

```bash
# 1. Run migrations
cd c:\xampp\htdocs\UKK\p2
php artisan migrate

# 2. Seed sample data
php artisan db:seed --class=TaskAssignmentSeeder

# 3. Start server
php artisan serve --port=8001
```

---

## 🧪 Testing with Postman/Browser

### Test Admin Flow
1. Login as admin
2. Create project with assigned_to = team lead ID
3. View projects (should see only your created projects)
4. Try to create board → Should fail (permission denied)

### Test Team Lead Flow
1. Login as teamlead
2. View projects (should see only assigned projects)
3. Create board in assigned project
4. Create card and assign to member
5. Try to create project → Should fail

### Test Member Flow
1. Login as desainer/developer
2. View my tasks (should see only assigned tasks)
3. Update task status
4. Add comment
5. Try to create card → Should fail

---

## 📈 Summary

| Action | Admin | Team Lead | Member |
|--------|-------|-----------|--------|
| Create Project | ✅ | ❌ | ❌ |
| Assign Project | ✅ | ❌ | ❌ |
| Create Board | ❌ | ✅ | ❌ |
| Create Card | ❌ | ✅ | ❌ |
| Assign Card | ❌ | ✅ | ❌ |
| Update Task Status | ❌ | ✅ | ✅ (own tasks) |
| View All Projects | ❌ | ❌ | ❌ |
| View Assigned Projects | Admin's | Team Lead's | Via Tasks |
| Dashboard | Projects | Projects/Boards/Cards | Tasks |

---

## 🎉 Complete Hierarchy

```
🔴 ADMIN
  └─ Creates PROJECT
      └─ Assigns to TEAM LEAD
           │
           └─ 🟠 TEAM LEAD
                ├─ Creates BOARD
                └─ Creates CARD
                     └─ Assigns to MEMBER
                          │
                          └─ 🟢 MEMBER (Designer/Developer)
                               ├─ Works on TASK
                               ├─ Updates STATUS
                               └─ Adds COMMENTS
```

---

## 🔧 Maintenance Commands

```bash
# Fresh install
php artisan migrate:fresh --seed --seeder=TaskAssignmentSeeder

# Check migration status
php artisan migrate:status

# Rollback last migration
php artisan migrate:rollback

# View routes
php artisan route:list --path=api
```
