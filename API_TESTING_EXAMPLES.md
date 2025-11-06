# API Testing Examples

## Setup
Base URL: `http://localhost/p2/public/api`

## 1. Register User

```bash
curl -X POST http://localhost/p2/public/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "fullname": "Flutter Developer",
    "username": "flutterdev",
    "email": "flutter@example.com",
    "password": "password123",
    "role": "developer"
  }'
```

Response:
```json
{
  "success": true,
  "message": "Registration successful",
  "data": {
    "user": {...},
    "token": "1|abc123..."
  }
}
```

Save the token for next requests!

## 2. Login

```bash
curl -X POST http://localhost/p2/public/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "login": "flutter@example.com",
    "password": "password123"
  }'
```

## 3. Get User Profile

```bash
curl -X GET http://localhost/p2/public/api/user \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

## 4. Get Dashboard Stats

```bash
curl -X GET http://localhost/p2/public/api/dashboard/stats \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

## 5. Get All Projects

```bash
curl -X GET http://localhost/p2/public/api/projects \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

## 6. Create Project (Team Lead/Admin only)

```bash
curl -X POST http://localhost/p2/public/api/projects \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -d '{
    "project_name": "Mobile App Development",
    "description": "Flutter mobile application",
    "deadline": "2025-12-31"
  }'
```

## 7. Get Boards in Project

```bash
curl -X GET http://localhost/p2/public/api/projects/1/boards \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

## 8. Create Board

```bash
curl -X POST http://localhost/p2/public/api/projects/1/boards \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -d '{
    "board_name": "Sprint 1 - Authentication",
    "description": "First sprint focusing on authentication features"
  }'
```

## 9. Get Cards in Board

```bash
curl -X GET http://localhost/p2/public/api/boards/1/cards \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

Filter by status:
```bash
curl -X GET "http://localhost/p2/public/api/boards/1/cards?status=in_progress" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

Get my assigned cards only:
```bash
curl -X GET "http://localhost/p2/public/api/boards/1/cards?assigned_to_me=true" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

## 10. Create Card (Task)

```bash
curl -X POST http://localhost/p2/public/api/boards/1/cards \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -d '{
    "card_title": "Implement Login Screen",
    "description": "Create login UI with email and password fields",
    "due_date": "2025-11-15",
    "priority": "high",
    "status": "todo",
    "estimated_hours": 8,
    "assignees": [2, 3]
  }'
```

## 11. Get My Assigned Cards

```bash
curl -X GET http://localhost/p2/public/api/my-cards \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

## 12. Update Card Status

```bash
curl -X PUT http://localhost/p2/public/api/cards/1/status \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -d '{
    "status": "in_progress"
  }'
```

## 13. Assign User to Card

```bash
curl -X POST http://localhost/p2/public/api/cards/1/assign \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -d '{
    "user_id": 2
  }'
```

## 14. Create Subtask

```bash
curl -X POST http://localhost/p2/public/api/cards/1/subtasks \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -d '{
    "subtask_title": "Create login form widget",
    "description": "Build the UI components for login form",
    "status": "todo",
    "estimated_hours": 2
  }'
```

## 15. Get Comments

```bash
curl -X GET http://localhost/p2/public/api/cards/1/comments \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

## 16. Add Comment

```bash
curl -X POST http://localhost/p2/public/api/cards/1/comments \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -d '{
    "comment_text": "I think we should use Provider for state management",
    "comment_type": "card"
  }'
```

## 17. Update Profile

```bash
curl -X PUT http://localhost/p2/public/api/profile \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -d '{
    "fullname": "Updated Flutter Developer",
    "email": "newflutter@example.com"
  }'
```

## 18. Logout

```bash
curl -X POST http://localhost/p2/public/api/logout \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

## Testing Workflow

### For Developer/Designer (Member Role):

1. **Register/Login** → Get token
2. **Get Dashboard Stats** → See assigned cards count
3. **Get My Cards** → See all assigned tasks
4. **Get Card Details** → View specific task with subtasks and comments
5. **Update Card Status** → Mark progress (todo → in_progress → done)
6. **Add Comments** → Communicate with team
7. **Update Subtasks** → Track detailed progress

### For Team Lead:

1. **Register/Login** → Get token
2. **Create Project** → Start new project
3. **Create Boards** → Organize sprints/phases
4. **Create Cards** → Add tasks
5. **Assign Users** → Assign tasks to team members
6. **Get Dashboard Stats** → Monitor overall progress
7. **Track Progress** → View cards by status

### For Admin:

1. **Login** → Use admin credentials
2. **Get Dashboard Stats** → System-wide statistics
3. **View All Projects** → Monitor all projects
4. **Manage Users** → (Use web interface for CRUD)

## Postman Collection

Import this JSON to Postman for easy testing:

1. Create new collection "Project Management API"
2. Set base URL variable: `{{base_url}}` = `http://localhost/p2/public/api`
3. Set token variable: `{{token}}` = Your auth token
4. Add Authorization header: `Bearer {{token}}`

## Common HTTP Status Codes

- **200 OK** - Success
- **201 Created** - Resource created successfully
- **401 Unauthorized** - Invalid or missing token
- **403 Forbidden** - No permission for this action
- **404 Not Found** - Resource not found
- **422 Unprocessable Entity** - Validation error

## Tips for Flutter Integration

1. **Use dio or http package** for HTTP requests
2. **Store token securely** using flutter_secure_storage
3. **Implement interceptor** to auto-add Authorization header
4. **Handle errors properly** - check `success` field
5. **Parse JSON responses** to Dart models
6. **Implement offline caching** for better UX
7. **Use state management** (Provider, Bloc, Riverpod)
8. **Show loading indicators** during API calls
9. **Handle token expiration** and re-login
10. **Implement pull-to-refresh** for data updates
