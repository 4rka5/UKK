# API Documentation untuk Flutter

Base URL: `http://localhost/p2/public/api`

## Authentication

### Register
**POST** `/register`

Request Body:
```json
{
  "fullname": "John Doe",
  "username": "johndoe",
  "email": "john@example.com",
  "password": "password123",
  "role": "designer" // optional: designer or developer
}
```

Response:
```json
{
  "success": true,
  "message": "Registration successful",
  "data": {
    "user": {
      "id": 1,
      "fullname": "John Doe",
      "username": "johndoe",
      "email": "john@example.com",
      "role": "designer",
      "status": "active"
    },
    "token": "1|abc123..."
  }
}
```

### Login
**POST** `/login`

Request Body:
```json
{
  "login": "john@example.com", // email or username
  "password": "password123"
}
```

Response:
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": {...},
    "token": "2|xyz789..."
  }
}
```

### Logout
**POST** `/logout`

Headers: `Authorization: Bearer {token}`

Response:
```json
{
  "success": true,
  "message": "Logged out successfully"
}
```

### Get User Profile
**GET** `/user`

Headers: `Authorization: Bearer {token}`

Response:
```json
{
  "success": true,
  "data": {
    "id": 1,
    "fullname": "John Doe",
    "username": "johndoe",
    "email": "john@example.com",
    "role": "designer"
  }
}
```

### Update Profile
**PUT** `/profile`

Headers: `Authorization: Bearer {token}`

Request Body:
```json
{
  "fullname": "John Updated",
  "email": "newemail@example.com",
  "current_password": "oldpass", // required if changing password
  "password": "newpass123" // optional
}
```

## Projects

### Get All Projects
**GET** `/projects`

Headers: `Authorization: Bearer {token}`

Response:
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "project_name": "Project Alpha",
      "description": "Description here",
      "deadline": "2025-12-31",
      "created_by": 1,
      "owner": {
        "id": 1,
        "fullname": "Team Lead Name"
      }
    }
  ]
}
```

### Create Project
**POST** `/projects`

Headers: `Authorization: Bearer {token}`

Request Body:
```json
{
  "project_name": "New Project",
  "description": "Project description",
  "deadline": "2025-12-31"
}
```

### Get Single Project
**GET** `/projects/{id}`

### Update Project
**PUT** `/projects/{id}`

### Delete Project
**DELETE** `/projects/{id}`

## Boards

### Get Boards in Project
**GET** `/projects/{projectId}/boards`

### Create Board
**POST** `/projects/{projectId}/boards`

Request Body:
```json
{
  "board_name": "Sprint 1",
  "description": "First sprint board"
}
```

### Get Single Board
**GET** `/projects/{projectId}/boards/{boardId}`

### Update Board
**PUT** `/projects/{projectId}/boards/{boardId}`

### Delete Board
**DELETE** `/projects/{projectId}/boards/{boardId}`

## Cards (Tasks)

### Get Cards in Board
**GET** `/boards/{boardId}/cards`

Query Parameters:
- `status` - Filter by status (backlog, todo, in_progress, code_review, testing, done)
- `assigned_to_me` - Filter cards assigned to current user (true/false)

Response:
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "card_title": "Implement Login",
      "description": "Create login page",
      "status": "in_progress",
      "priority": "high",
      "due_date": "2025-11-15",
      "estimated_hours": 8,
      "actual_hours": null,
      "assignees": [
        {
          "id": 2,
          "fullname": "Developer Name"
        }
      ],
      "subtasks": [],
      "board": {
        "id": 1,
        "board_name": "Sprint 1",
        "project": {
          "id": 1,
          "project_name": "Project Alpha"
        }
      }
    }
  ]
}
```

### Create Card
**POST** `/boards/{boardId}/cards`

Request Body:
```json
{
  "card_title": "Task Title",
  "description": "Task description",
  "due_date": "2025-11-15",
  "priority": "high", // low, medium, high
  "status": "todo", // backlog, todo, in_progress, code_review, testing, done
  "estimated_hours": 8,
  "assignees": [2, 3] // optional: array of user IDs
}
```

### Get Single Card
**GET** `/boards/{boardId}/cards/{cardId}`

Response includes full details with assignees, subtasks, and comments.

### Update Card
**PUT** `/boards/{boardId}/cards/{cardId}`

Request Body:
```json
{
  "card_title": "Updated Title",
  "status": "in_progress",
  "actual_hours": 5,
  "assignees": [2, 3]
}
```

### Delete Card
**DELETE** `/boards/{boardId}/cards/{cardId}`

### Update Card Status Only
**PUT** `/cards/{cardId}/status`

Request Body:
```json
{
  "status": "done"
}
```

### Assign User to Card
**POST** `/cards/{cardId}/assign`

Request Body:
```json
{
  "user_id": 2
}
```

### Unassign User from Card
**DELETE** `/cards/{cardId}/unassign/{userId}`

## Subtasks

### Get Subtasks
**GET** `/cards/{cardId}/subtasks`

### Create Subtask
**POST** `/cards/{cardId}/subtasks`

Request Body:
```json
{
  "subtask_title": "Subtask title",
  "description": "Description",
  "status": "todo", // todo, in_progress, done
  "estimated_hours": 2
}
```

### Update Subtask
**PUT** `/cards/{cardId}/subtasks/{subtaskId}`

### Delete Subtask
**DELETE** `/cards/{cardId}/subtasks/{subtaskId}`

## Comments

### Get Comments
**GET** `/cards/{cardId}/comments`

Response:
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "comment_text": "This is a comment",
      "comment_type": "card",
      "user": {
        "id": 1,
        "fullname": "John Doe"
      },
      "created_at": "2025-11-06T10:30:00Z"
    }
  ]
}
```

### Create Comment
**POST** `/cards/{cardId}/comments`

Request Body:
```json
{
  "comment_text": "This is my comment",
  "comment_type": "card", // card or subtask
  "subtask_id": null // optional, if commenting on subtask
}
```

### Update Comment
**PUT** `/cards/{cardId}/comments/{commentId}`

Only the comment owner can update.

### Delete Comment
**DELETE** `/cards/{cardId}/comments/{commentId}`

Only the comment owner can delete.

## Dashboard Stats

### Get Dashboard Statistics
**GET** `/dashboard/stats`

Headers: `Authorization: Bearer {token}`

Response varies by role:

**Admin:**
```json
{
  "success": true,
  "data": {
    "total_users": 25,
    "total_projects": 10,
    "total_boards": 30,
    "total_cards": 150
  }
}
```

**Team Lead:**
```json
{
  "success": true,
  "data": {
    "my_projects": 5,
    "my_boards": 15,
    "total_cards": 75,
    "cards_by_status": [
      {"status": "todo", "count": 20},
      {"status": "in_progress", "count": 15},
      {"status": "done", "count": 40}
    ]
  }
}
```

**Member (Designer/Developer):**
```json
{
  "success": true,
  "data": {
    "assigned_cards": 12,
    "cards_by_status": [...],
    "cards_by_priority": [
      {"priority": "high", "count": 5},
      {"priority": "medium", "count": 4},
      {"priority": "low", "count": 3}
    ]
  }
}
```

### Get My Assigned Cards
**GET** `/my-cards`

Headers: `Authorization: Bearer {token}`

Returns all cards assigned to the current user with full details.

## Error Responses

All endpoints may return error responses:

**Validation Error (422):**
```json
{
  "success": false,
  "message": "Validation error",
  "errors": {
    "email": ["The email field is required."]
  }
}
```

**Unauthorized (401):**
```json
{
  "success": false,
  "message": "Invalid login credentials"
}
```

**Forbidden (403):**
```json
{
  "success": false,
  "message": "Unauthorized"
}
```

**Not Found (404):**
```json
{
  "message": "Resource not found"
}
```

## Status & Priority Values

### Card Status
- `backlog`
- `todo`
- `in_progress`
- `code_review`
- `testing`
- `done`

### Card Priority
- `low`
- `medium`
- `high`

### Subtask Status
- `todo`
- `in_progress`
- `done`

### User Roles
- `admin`
- `team_lead`
- `designer`
- `developer`

## Notes for Flutter Implementation

1. **Base URL**: Ganti dengan URL server Anda
2. **Headers**: Semua authenticated request butuh header `Authorization: Bearer {token}`
3. **Content-Type**: `application/json` untuk semua request
4. **Token Storage**: Simpan token di secure storage (flutter_secure_storage)
5. **Error Handling**: Check `success` field di response
6. **Pagination**: Belum diimplementasi, bisa ditambahkan jika diperlukan

## Example Flutter HTTP Request

```dart
import 'package:http/http.dart' as http;
import 'dart:convert';

// Login
Future<Map<String, dynamic>> login(String email, String password) async {
  final response = await http.post(
    Uri.parse('http://localhost/p2/public/api/login'),
    headers: {'Content-Type': 'application/json'},
    body: jsonEncode({
      'login': email,
      'password': password,
    }),
  );
  
  return jsonDecode(response.body);
}

// Get Projects with Auth
Future<List> getProjects(String token) async {
  final response = await http.get(
    Uri.parse('http://localhost/p2/public/api/projects'),
    headers: {
      'Content-Type': 'application/json',
      'Authorization': 'Bearer $token',
    },
  );
  
  final data = jsonDecode(response.body);
  return data['success'] ? data['data'] : [];
}
```
