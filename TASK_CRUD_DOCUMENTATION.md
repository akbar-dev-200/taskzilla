# Task CRUD Operations Documentation

## 📋 Overview
Complete Task management system with CRUD operations, multi-user assignment, status tracking, and comprehensive filtering.

## 🏗️ Architecture

### Files Created/Modified:

1. **Migration**: `database/migrations/2026_01_18_161024_create_task_assignees_table.php`
2. **Model**: `app/Models/Task.php` (updated with assignees relationship)
3. **Gates**: `app/AccessControl/Gates/TaskGates.php`
4. **Service**: `app/Services/Module/Task/TaskService.php`
5. **Request**: `app/Http/Requests/Task/TaskRequest.php`
6. **Controller**: `app/Http/Controllers/Task/TaskController.php`
7. **Routes**: `routes/api.php` (updated with task routes)
8. **Provider**: `app/Providers/AppServiceProvider.php` (registered TaskGates)

---

## 📊 Database Schema

### task_assignees Table (Pivot)
```sql
- id (bigint, primary key)
- uuid (uuid, unique)
- task_id (foreign key → tasks.id, cascade on delete)
- user_id (foreign key → users.id, cascade on delete)
- assigned_at (timestamp, nullable)
- created_at, updated_at (timestamps)
- unique constraint on (task_id, user_id)
```

**Purpose**: Enables assigning multiple users to a single task.

---

## 🔐 Authorization (Gates)

| Gate | Who Can Access | Purpose |
|------|----------------|---------|
| `create-task` | Team members | Create task in team |
| `view-task` | Team members or assignees | View task details |
| `update-task` | Team lead, admin, or task creator | Update task details |
| `delete-task` | Team lead or admin | Delete task |
| `update-task-status` | Team lead, admin, or assignees | Update task status |
| `manage-task-assignees` | Team lead, admin, or task creator | Assign/remove users |

---

## 🎯 API Endpoints

### 1. List Team Tasks
**GET** `/api/tasks/team/{teamId}`

Lists all tasks for a specific team with filtering and pagination.

**Authorization**: Team members

**Query Parameters**:
```
status: pending|in_progress|completed
priority: low|medium|high
assigned_to: user_id
assigned_by: user_id
team_id: team_id
overdue: true|false
due_soon: true|false (due in next 7 days)
sort_by: created_at|due_date|priority|status|title
sort_order: asc|desc
per_page: 1-100 (default: 10)
```

**Example Request**:
```bash
GET http://127.0.0.1:8001/api/tasks/team/1?status=in_progress&priority=high&per_page=20
Authorization: Bearer {token}
```

**Response**:
```json
{
  "success": true,
  "message": "Tasks retrieved successfully",
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "uuid": "550e8400-e29b-41d4-a716-446655440000",
        "title": "Implement user authentication",
        "description": "Add JWT authentication...",
        "status": "in_progress",
        "priority": "high",
        "due_date": "2026-01-25",
        "team_id": 1,
        "assigned_by": 1,
        "comments_count": 5,
        "files_count": 2,
        "assignees_count": 2,
        "assigned_by_user": {
          "id": 1,
          "name": "John Doe",
          "email": "john@example.com"
        },
        "assignees": [
          {
            "id": 2,
            "name": "Jane Smith",
            "email": "jane@example.com"
          }
        ],
        "team": {
          "id": 1,
          "name": "Development Team"
        }
      }
    ],
    "per_page": 20,
    "total": 15
  }
}
```

---

### 2. Get My Tasks
**GET** `/api/tasks/my-tasks`

Lists all tasks assigned to the authenticated user across all teams.

**Authorization**: Any authenticated user

**Query Parameters**: Same as List Team Tasks

**Example Request**:
```bash
GET http://127.0.0.1:8001/api/tasks/my-tasks?status=pending
Authorization: Bearer {token}
```

---

### 3. Create Task
**POST** `/api/tasks`

Creates a new task in a team.

**Authorization**: Team members

**Request Body**:
```json
{
  "title": "Implement user authentication",
  "description": "Add JWT authentication to the API",
  "status": "pending",
  "priority": "high",
  "due_date": "2026-01-25",
  "team_id": 1,
  "assignee_ids": [2, 3, 4]
}
```

**Validation Rules**:
- `title`: required, string, 3-255 characters
- `description`: nullable, string, max 5000 characters
- `status`: nullable, enum (pending, in_progress, completed)
- `priority`: nullable, enum (low, medium, high)
- `due_date`: nullable, date, must be today or future
- `team_id`: required, must exist in teams table
- `assignee_ids`: nullable, array of valid user IDs

**Business Logic**:
- `assigned_by` is automatically set to current user
- `uuid` is automatically generated
- Default status: `pending`
- Default priority: `medium`
- Assigns users to task if `assignee_ids` provided

**Response** (201 Created):
```json
{
  "success": true,
  "message": "Task created successfully",
  "data": {
    "id": 1,
    "uuid": "550e8400-e29b-41d4-a716-446655440000",
    "title": "Implement user authentication",
    "description": "Add JWT authentication to the API",
    "status": "pending",
    "priority": "high",
    "due_date": "2026-01-25",
    "team_id": 1,
    "assigned_by": 1,
    "created_at": "2026-01-18T10:00:00.000000Z",
    "assigned_by_user": {...},
    "assignees": [...],
    "team": {...}
  }
}
```

---

### 4. Show Task Details
**GET** `/api/tasks/{task_uuid}`

Gets detailed information about a task including comments and files.

**Authorization**: Team members or assignees

**Example Request**:
```bash
GET http://127.0.0.1:8001/api/tasks/550e8400-e29b-41d4-a716-446655440000
Authorization: Bearer {token}
```

**Response**:
```json
{
  "success": true,
  "message": "Task details retrieved successfully",
  "data": {
    "id": 1,
    "uuid": "550e8400-e29b-41d4-a716-446655440000",
    "title": "Implement user authentication",
    "description": "Add JWT authentication...",
    "status": "in_progress",
    "priority": "high",
    "due_date": "2026-01-25",
    "comments_count": 5,
    "files_count": 2,
    "assignees_count": 2,
    "assigned_by_user": {...},
    "assignees": [
      {
        "id": 2,
        "name": "Jane Smith",
        "email": "jane@example.com",
        "pivot": {
          "uuid": "660e8400-e29b-41d4-a716-446655440000",
          "assigned_at": "2026-01-18T10:00:00.000000Z"
        }
      }
    ],
    "team": {...},
    "comments": [
      {
        "id": 1,
        "content": "Started working on this",
        "user": {
          "id": 2,
          "name": "Jane Smith",
          "email": "jane@example.com"
        },
        "created_at": "2026-01-18T11:00:00.000000Z"
      }
    ],
    "files": [...]
  }
}
```

---

### 5. Update Task
**PUT/PATCH** `/api/tasks/{task_uuid}`

Updates task details.

**Authorization**: Team lead, admin, or task creator

**Request Body** (all fields optional):
```json
{
  "title": "Updated title",
  "description": "Updated description",
  "status": "in_progress",
  "priority": "high",
  "due_date": "2026-01-30"
}
```

**Response** (200 OK):
```json
{
  "success": true,
  "message": "Task updated successfully",
  "data": {...}
}
```

---

### 6. Update Task Status
**PATCH** `/api/tasks/{task_uuid}/status`

Updates only the task status (separate endpoint for convenience).

**Authorization**: Team lead, admin, or assignees

**Request Body**:
```json
{
  "status": "completed"
}
```

**Use Cases**:
- Assignee marks task as completed
- Team lead moves task to in_progress
- Admin changes task status

**Response** (200 OK):
```json
{
  "success": true,
  "message": "Task status updated successfully",
  "data": {...}
}
```

---

### 7. Delete Task
**DELETE** `/api/tasks/{task_uuid}`

Deletes a task and all its associations.

**Authorization**: Team lead or admin

**Cascade Behavior**:
- ✅ Removes all assignees (detaches)
- ✅ Deletes all comments (cascade)
- ✅ Deletes all files (cascade)

**Example Request**:
```bash
DELETE http://127.0.0.1:8001/api/tasks/550e8400-e29b-41d4-a716-446655440000
Authorization: Bearer {token}
```

**Response** (200 OK):
```json
{
  "success": true,
  "message": "Task deleted successfully"
}
```

---

### 8. Assign Users to Task
**POST** `/api/tasks/{task_uuid}/assign`

Assigns one or more users to a task.

**Authorization**: Team lead, admin, or task creator

**Request Body**:
```json
{
  "user_ids": [2, 3, 4]
}
```

**Validation**:
- All users must exist
- All users must be members of the task's team
- Uses `syncWithoutDetaching` - doesn't remove existing assignees

**Response** (200 OK):
```json
{
  "success": true,
  "message": "Users assigned to task successfully",
  "data": {
    "task_id": 1,
    "task_uuid": "550e8400-e29b-41d4-a716-446655440000",
    "assignees": [
      {
        "id": 2,
        "name": "Jane Smith",
        "email": "jane@example.com"
      },
      {
        "id": 3,
        "name": "Bob Johnson",
        "email": "bob@example.com"
      }
    ]
  }
}
```

---

### 9. Remove Assignees from Task
**POST** `/api/tasks/{task_uuid}/remove-assignees`

Removes one or more users from a task.

**Authorization**: Team lead, admin, or task creator

**Request Body**:
```json
{
  "user_ids": [3, 4]
}
```

**Response** (200 OK):
```json
{
  "success": true,
  "message": "Users removed from task successfully",
  "data": {
    "task_id": 1,
    "task_uuid": "550e8400-e29b-41d4-a716-446655440000",
    "assignees": [...]
  }
}
```

---

### 10. Get Task Statistics
**GET** `/api/tasks/team/{teamId}/statistics`

Gets task statistics for a team.

**Authorization**: Team members

**Example Request**:
```bash
GET http://127.0.0.1:8001/api/tasks/team/1/statistics
Authorization: Bearer {token}
```

**Response** (200 OK):
```json
{
  "success": true,
  "message": "Task statistics retrieved successfully",
  "data": {
    "total": 25,
    "pending": 8,
    "in_progress": 12,
    "completed": 5,
    "overdue": 3,
    "high_priority": 7
  }
}
```

---

## 🔄 Complete Task Workflow

### Scenario 1: Create and Assign Task
```bash
# 1. Team lead creates task
POST /api/tasks
{
  "title": "Build authentication",
  "team_id": 1,
  "priority": "high",
  "assignee_ids": [2, 3]
}

# 2. View task details
GET /api/tasks/{task_uuid}

# 3. Assignee updates status
PATCH /api/tasks/{task_uuid}/status
{
  "status": "in_progress"
}

# 4. Add more assignees
POST /api/tasks/{task_uuid}/assign
{
  "user_ids": [4]
}

# 5. Mark as completed
PATCH /api/tasks/{task_uuid}/status
{
  "status": "completed"
}
```

### Scenario 2: Manage My Tasks
```bash
# 1. Get all my tasks
GET /api/tasks/my-tasks

# 2. Filter by status
GET /api/tasks/my-tasks?status=in_progress

# 3. View task details
GET /api/tasks/{task_uuid}

# 4. Update task status
PATCH /api/tasks/{task_uuid}/status
{
  "status": "completed"
}
```

---

## 🎨 Service Methods

### TaskService Methods:

| Method | Purpose | Transaction |
|--------|---------|-------------|
| `getTeamTasks()` | List filtered tasks for team | No |
| `getUserTasks()` | List tasks for user | No |
| `createTask()` | Create task + assign users | ✅ Yes |
| `getTaskDetails()` | Get task with relationships | No |
| `updateTask()` | Update task fields | No |
| `updateTaskStatus()` | Update status only | No |
| `deleteTask()` | Delete task + detach assignees | ✅ Yes |
| `assignUsers()` | Add users to task | No |
| `removeUsers()` | Remove users from task | No |
| `syncAssignees()` | Replace all assignees | No |
| `getTaskStatistics()` | Get team stats | No |

---

## 💡 Key Features

### 1. Multi-User Assignment
- ✅ Single task can have multiple assignees
- ✅ Uses `task_assignees` pivot table with UUID
- ✅ Tracks assignment timestamp
- ✅ Prevents duplicate assignments

### 2. Comprehensive Filtering
- ✅ Filter by status, priority, assignee, creator
- ✅ Find overdue tasks
- ✅ Find tasks due soon (next 7 days)
- ✅ Flexible sorting

### 3. Smart Authorization
- ✅ Team-based permissions
- ✅ Role-based access (admin, lead, member)
- ✅ Creator/assignee privileges
- ✅ Granular control per action

### 4. Relationship Loading
- ✅ Eager loading to prevent N+1 queries
- ✅ Counts for comments, files, assignees
- ✅ Nested relationships (team, users, etc.)

### 5. Status Management
- ✅ Dedicated status update endpoint
- ✅ Assignees can update their task status
- ✅ Track status changes

---

## 🔒 Security Features

1. **UUID-based URLs**: Tasks are accessed via UUID, not integer ID
2. **Gate Authorization**: Every action checks permissions
3. **Team Validation**: Users can only be assigned if they're team members
4. **Soft Validation**: Comprehensive input validation
5. **Transaction Safety**: Critical operations use DB transactions

---

## 📊 Database Indexes

### tasks table:
- `uuid` (indexed)
- `status` (indexed)
- `priority` (indexed)
- `due_date` (indexed)
- `team_id` (indexed)
- `assigned_by` (indexed)

### task_assignees table:
- `task_id` (indexed)
- `user_id` (indexed)
- `uuid` (indexed)
- `unique(task_id, user_id)`

---

## 🚀 Quick Start

### 1. Run Migration
```bash
php artisan migrate
```

### 2. Create a Task
```bash
POST /api/tasks
{
  "title": "My First Task",
  "team_id": 1,
  "assignee_ids": [2]
}
```

### 3. List Tasks
```bash
GET /api/tasks/team/1
```

### 4. Update Status
```bash
PATCH /api/tasks/{uuid}/status
{
  "status": "completed"
}
```

---

## ✅ Implementation Checklist

- [x] Create task_assignees migration
- [x] Update Task model with relationships
- [x] Create TaskGates for authorization
- [x] Implement TaskService with business logic
- [x] Create TaskRequest for validation
- [x] Build TaskController with CRUD methods
- [x] Add API routes
- [x] Register gates in AppServiceProvider
- [x] Support multi-user assignment
- [x] Implement filtering and sorting
- [x] Add statistics endpoint
- [x] Handle status updates
- [x] Transaction safety for critical operations

---

## 📚 Additional Notes

1. **UUID**: All tasks use UUID for route binding for security
2. **Soft Deletes**: Not implemented - tasks are hard deleted
3. **Audit Trail**: Consider adding activity log for task changes
4. **Notifications**: Consider adding notifications when assigned to task
5. **Due Date Reminders**: Can implement scheduled job for reminders
6. **Task Dependencies**: Not implemented - can be added later
7. **Subtasks**: Not implemented - can be added as nested tasks

---

**System is production-ready with clean code, proper authorization, and comprehensive error handling!** 🎉
