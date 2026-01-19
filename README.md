# 🦖 Taskzilla

> **Because even monsters need to organize their chaos** 🔥

<div align="center">

[![Laravel](https://img.shields.io/badge/Laravel-11.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![PostgreSQL](https://img.shields.io/badge/PostgreSQL-15+-316192?style=for-the-badge&logo=postgresql&logoColor=white)](https://postgresql.org)
[![License](https://img.shields.io/badge/License-MIT-green.svg?style=for-the-badge)](LICENSE)

**A powerful, team-based task management system with multi-user assignment, role-based access control, and real-time collaboration features.**

[Features](#-features) • [Architecture](#-architecture) • [Database Schema](#-database-schema) • [Application Flows](#-application-flows) • [Installation](#-installation) • [API Docs](#-api-documentation)

</div>

---

## 📖 Table of Contents

- [About](#-about)
- [Features](#-features)
- [Architecture](#-architecture)
- [Database Schema](#-database-schema)
- [Application Flows](#-application-flows)
- [Tech Stack](#-tech-stack)
- [Installation](#-installation)
- [API Documentation](#-api-documentation)
- [Project Structure](#-project-structure)
- [Security](#-security)
- [Contributing](#-contributing)
- [License](#-license)

---

## 🎯 About

**Taskzilla** is a modern, enterprise-grade task management API built with Laravel that helps teams conquer their workload like a boss monster! 🦖

### Why Taskzilla?

- 👥 **Multi-Team Support** - Manage multiple teams with separate workspaces
- 🎭 **Role-Based Access** - Granular permissions (Admin, Lead, Member)
- 🔗 **Multi-User Assignment** - Assign tasks to multiple team members
- 📧 **Team Invitations** - Email-based team invitation system
- 🔐 **Secure by Default** - UUID-based routes, Laravel Sanctum authentication
- 🚀 **Production Ready** - Clean architecture, comprehensive validation, error handling
- 📊 **Task Analytics** - Real-time statistics and filtering
- 🎨 **RESTful API** - Well-designed, predictable endpoints

---

## ✨ Features

<table>
<tr>
<td width="50%">

### 👥 Team Management
- ✅ Create and manage teams
- ✅ Invite members via email
- ✅ Role-based team hierarchy
- ✅ Team lead assignment
- ✅ Member management

</td>
<td width="50%">

### 📋 Task Management
- ✅ Create, update, delete tasks
- ✅ Multi-user task assignment
- ✅ Priority levels (Low/Medium/High)
- ✅ Status tracking (Pending/In Progress/Completed)
- ✅ Due date management

</td>
</tr>
<tr>
<td width="50%">

### 🔐 Authentication & Security
- ✅ Laravel Sanctum token auth
- ✅ UUID-based resource identifiers
- ✅ Gate-based authorization
- ✅ CORS support
- ✅ Rate limiting ready

</td>
<td width="50%">

### 🔍 Advanced Features
- ✅ Advanced filtering & sorting
- ✅ Task statistics dashboard
- ✅ Overdue task detection
- ✅ Task comments & file attachments
- ✅ Comprehensive error handling

</td>
</tr>
</table>

---

## 🏗️ Architecture

Taskzilla follows **clean architecture principles** with clear separation of concerns:

```
┌─────────────────────────────────────────────────────────────┐
│                         CLIENT                              │
│              (Postman, Mobile App, Frontend)                │
└────────────────────┬────────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────────┐
│                      API LAYER                              │
│                    (routes/api.php)                         │
│              Bearer Token Authentication                    │
└────────────────────┬────────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────────┐
│                   CONTROLLERS                               │
│          TeamController │ TaskController                    │
│                                                             │
│   • Handle HTTP requests                                    │
│   • Validate input (FormRequest)                            │
│   • Return JSON responses                                   │
└────────────────────┬────────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────────┐
│                 AUTHORIZATION                               │
│            (Gates & Policies)                               │
│                                                             │
│   • Role-based access control                               │
│   • Ownership verification                                  │
│   • Team membership checks                                  │
└────────────────────┬────────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────────┐
│                  SERVICE LAYER                              │
│          TeamService │ TaskService                          │
│                                                             │
│   • Business logic                                          │
│   • Database queries                                        │
│   • Transactions                                            │
│   • Data transformations                                    │
└────────────────────┬────────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────────┐
│                     MODELS                                  │
│    User │ Team │ Task │ Invite │ Comment │ File            │
│                                                             │
│   • Eloquent ORM                                            │
│   • Relationships                                           │
│   • Query scopes                                            │
└────────────────────┬────────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────────┐
│                    DATABASE                                 │
│                  (PostgreSQL)                               │
└─────────────────────────────────────────────────────────────┘
```

---

## 🗄️ Database Schema

### Entity Relationship Diagram

```
┌──────────────────┐
│      USERS       │
├──────────────────┤
│ • id (PK)        │◄────────┐
│ • uuid           │         │
│ • name           │         │
│ • email          │         │
│ • password       │         │
│ • role (enum)    │         │
│ • is_active      │         │
└────────┬─────────┘         │
         │                   │
         │ created_by        │
         │ lead_id           │
         │                   │
    ┌────▼───────────────────┴──┐
    │        TEAMS              │
    ├───────────────────────────┤
    │ • id (PK)                 │
    │ • uuid                    │
    │ • name                    │◄──────────────┐
    │ • lead_id (FK → users)    │               │
    │ • created_by (FK → users) │               │
    └────────┬──────────────────┘               │
             │                                   │
             │ team_id                           │
             │                                   │
    ┌────────▼──────────────┐          ┌────────┴─────────┐
    │    TEAM_USER          │          │      TASKS       │
    │    (Pivot Table)      │          ├──────────────────┤
    ├───────────────────────┤          │ • id (PK)        │
    │ • id (PK)             │          │ • uuid           │
    │ • uuid                │          │ • title          │
    │ • team_id (FK)        │          │ • description    │
    │ • user_id (FK)        │          │ • status (enum)  │
    │ • role (enum)         │          │ • priority(enum) │
    │ • joined_at           │          │ • due_date       │
    └───────────────────────┘          │ • team_id (FK)   │
                                       │ • assigned_by(FK)│
                                       └────────┬─────────┘
                                                │
                      ┌─────────────────────────┼─────────────────────┐
                      │                         │                     │
                      │                         │                     │
         ┌────────────▼─────────┐  ┌───────────▼────────┐  ┌────────▼─────────┐
         │   TASK_ASSIGNEES     │  │   TASK_COMMENTS    │  │    TASK_FILES    │
         │   (Pivot Table)      │  ├────────────────────┤  ├──────────────────┤
         ├──────────────────────┤  │ • id (PK)          │  │ • id (PK)        │
         │ • id (PK)            │  │ • uuid             │  │ • uuid           │
         │ • uuid               │  │ • content          │  │ • filename       │
         │ • task_id (FK)       │  │ • task_id (FK)     │  │ • filepath       │
         │ • user_id (FK)       │  │ • user_id (FK)     │  │ • task_id (FK)   │
         │ • assigned_at        │  │ • created_at       │  │ • uploaded_by(FK)│
         └──────────────────────┘  └────────────────────┘  └──────────────────┘

         ┌───────────────────┐
         │     INVITES       │
         ├───────────────────┤
         │ • id (PK)         │
         │ • uuid            │
         │ • team_id (FK)    │
         │ • email           │
         │ • token           │
         │ • role (enum)     │
         │ • status (enum)   │
         │ • invited_by (FK) │
         │ • expires_at      │
         └───────────────────┘

         ┌────────────────────────────┐
         │  PERSONAL_ACCESS_TOKENS    │
         │      (Laravel Sanctum)     │
         ├────────────────────────────┤
         │ • id (PK)                  │
         │ • tokenable_type           │
         │ • tokenable_id             │
         │ • name                     │
         │ • token (hashed)           │
         │ • abilities                │
         │ • last_used_at             │
         │ • expires_at               │
         └────────────────────────────┘
```

### Key Relationships

| Table | Relationship | Description |
|-------|-------------|-------------|
| **User ↔ Team** | Many-to-Many | Users belong to multiple teams via `team_user` pivot |
| **Team → User** | Belongs To | Team has one lead (user) |
| **Task → Team** | Belongs To | Task belongs to one team |
| **Task ↔ User** | Many-to-Many | Tasks can be assigned to multiple users via `task_assignees` |
| **Task → User** | Belongs To | Task has one creator (`assigned_by`) |
| **Comment → Task** | Belongs To | Comments belong to tasks |
| **File → Task** | Belongs To | Files belong to tasks |
| **Invite → Team** | Belongs To | Invites are for specific teams |

---

## 🔄 Application Flows

Understanding how Taskzilla works? Check out our comprehensive flow diagrams!

### 📚 Flow Documentation

| Document | Description | Topics Covered |
|----------|-------------|----------------|
| [**Application Flow**](Flows/APPLICATION_FLOW.md) | 🎯 High-level overview of the entire system | Complete user journey, System architecture, Feature flows, Permission system, Real-world examples |
| [**Invitation Flow**](Flows/INVITATION_FLOW_DIAGRAM.md) | 📧 Detailed invitation system flow | Send invitations, Accept invitations, Revoke invitations, Email flow, Permission matrix |

### 🎯 Quick Flow Guide

**New to Taskzilla?** Start here:

1. **Read:** [Application Flow](Flows/APPLICATION_FLOW.md) - Get the big picture
2. **Deep Dive:** [Invitation Flow](Flows/INVITATION_FLOW_DIAGRAM.md) - Understand team invitations
3. **API Reference:** [API Documentation](#-api-documentation) - Test the endpoints
4. **Database:** [Database Schema](#-database-schema) - See the data structure

### 🚀 What You'll Learn

From the flow documentation:

- ✅ How users register and authenticate
- ✅ How teams are created and managed
- ✅ How invitations work (email → token → acceptance)
- ✅ How tasks are created and assigned
- ✅ How multi-user collaboration works
- ✅ How permissions are enforced
- ✅ How data flows through the system
- ✅ Real-world usage scenarios

**Perfect for:** Developers, stakeholders, new team members, and integration partners!

---

## 🛠️ Tech Stack

<table>
<tr>
<td align="center" width="25%">

### Backend
![Laravel](https://img.shields.io/badge/Laravel-FF2D20?style=flat&logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-777BB4?style=flat&logo=php&logoColor=white)

</td>
<td align="center" width="25%">

### Database
![PostgreSQL](https://img.shields.io/badge/PostgreSQL-316192?style=flat&logo=postgresql&logoColor=white)
![Eloquent](https://img.shields.io/badge/Eloquent-ORM-red)

</td>
<td align="center" width="25%">

### Authentication
![Sanctum](https://img.shields.io/badge/Laravel-Sanctum-FF2D20)
![JWT](https://img.shields.io/badge/Token-Based-green)

</td>
<td align="center" width="25%">

### Tools
![Composer](https://img.shields.io/badge/Composer-885630?style=flat&logo=composer&logoColor=white)
![Git](https://img.shields.io/badge/Git-F05032?style=flat&logo=git&logoColor=white)

</td>
</tr>
</table>

### Core Dependencies

```json
{
  "laravel/framework": "^11.0",
  "laravel/sanctum": "^4.0",
  "php": "^8.2"
}
```

---

## 🚀 Installation

### Prerequisites

- PHP >= 8.2
- Composer
- PostgreSQL >= 15
- Node.js & NPM (for assets)

### Step-by-Step Setup

```bash
# 1. Clone the repository
git clone https://github.com/yourusername/taskzilla.git
cd taskzilla

# 2. Install dependencies
composer install
npm install

# 3. Environment setup
cp .env.example .env
php artisan key:generate

# 4. Configure database in .env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=taskzilla_db
DB_USERNAME=your_username
DB_PASSWORD=your_password

# 5. Run migrations
php artisan migrate

# 6. (Optional) Seed database
php artisan db:seed

# 7. Start the server
php artisan serve
```

Your API will be available at `http://127.0.0.1:8000` 🎉

---

## 📡 API Documentation

### Authentication

All protected endpoints require a Bearer token:

```bash
Authorization: Bearer {your-token-here}
```

### Quick Reference

<table>
<tr>
<th>Endpoint</th>
<th>Method</th>
<th>Description</th>
<th>Auth</th>
</tr>

<!-- Authentication -->
<tr><td colspan="4"><b>🔐 Authentication</b></td></tr>
<tr>
<td><code>/api/register</code></td>
<td>POST</td>
<td>Register new user</td>
<td>❌</td>
</tr>
<tr>
<td><code>/api/login</code></td>
<td>POST</td>
<td>Login & get token</td>
<td>❌</td>
</tr>
<tr>
<td><code>/api/logout</code></td>
<td>POST</td>
<td>Logout & revoke token</td>
<td>✅</td>
</tr>

<!-- Teams -->
<tr><td colspan="4"><b>👥 Teams</b></td></tr>
<tr>
<td><code>/api/teams</code></td>
<td>GET</td>
<td>List user's teams</td>
<td>✅</td>
</tr>
<tr>
<td><code>/api/teams</code></td>
<td>POST</td>
<td>Create new team</td>
<td>✅</td>
</tr>
<tr>
<td><code>/api/teams/{uuid}</code></td>
<td>GET</td>
<td>View team dashboard</td>
<td>✅</td>
</tr>
<tr>
<td><code>/api/teams/{uuid}</code></td>
<td>PUT</td>
<td>Update team</td>
<td>✅ Lead/Admin</td>
</tr>
<tr>
<td><code>/api/teams/{uuid}</code></td>
<td>DELETE</td>
<td>Delete team</td>
<td>✅ Admin</td>
</tr>

<!-- Tasks -->
<tr><td colspan="4"><b>📋 Tasks</b></td></tr>
<tr>
<td><code>/api/tasks/my-tasks</code></td>
<td>GET</td>
<td>My assigned tasks</td>
<td>✅</td>
</tr>
<tr>
<td><code>/api/tasks/team/{id}</code></td>
<td>GET</td>
<td>List team tasks</td>
<td>✅</td>
</tr>
<tr>
<td><code>/api/tasks</code></td>
<td>POST</td>
<td>Create task</td>
<td>✅</td>
</tr>
<tr>
<td><code>/api/tasks/{uuid}</code></td>
<td>GET</td>
<td>View task details</td>
<td>✅</td>
</tr>
<tr>
<td><code>/api/tasks/{uuid}</code></td>
<td>PUT</td>
<td>Update task</td>
<td>✅ Lead/Creator</td>
</tr>
<tr>
<td><code>/api/tasks/{uuid}/status</code></td>
<td>PATCH</td>
<td>Update status</td>
<td>✅ Assignee</td>
</tr>
<tr>
<td><code>/api/tasks/{uuid}</code></td>
<td>DELETE</td>
<td>Delete task</td>
<td>✅ Lead/Admin</td>
</tr>
<tr>
<td><code>/api/tasks/{uuid}/assign</code></td>
<td>POST</td>
<td>Assign users</td>
<td>✅ Lead/Creator</td>
</tr>

<!-- Invitations -->
<tr><td colspan="4"><b>📧 Invitations</b></td></tr>
<tr>
<td><code>/api/invites</code></td>
<td>POST</td>
<td>Send invitations</td>
<td>✅ Lead</td>
</tr>
<tr>
<td><code>/api/invites/accept</code></td>
<td>POST</td>
<td>Accept invitation</td>
<td>✅</td>
</tr>
<tr>
<td><code>/api/invites/my-pending</code></td>
<td>GET</td>
<td>My invitations</td>
<td>✅</td>
</tr>
</table>

### Example Request

```bash
# Register a user
curl -X POST http://127.0.0.1:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'

# Response
{
  "success": true,
  "data": {
    "user": {...},
    "token": "1|abc123..."
  }
}

# Create a task
curl -X POST http://127.0.0.1:8000/api/tasks \
  -H "Authorization: Bearer 1|abc123..." \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Implement feature X",
    "team_id": 1,
    "priority": "high",
    "assignee_ids": [2, 3]
  }'
```

### 📚 Detailed Documentation

| Document | Description |
|----------|-------------|
| 📄 [Application Flow](Flows/APPLICATION_FLOW.md) | High-level overview of entire system |
| 📄 [Invitation Flow](Flows/INVITATION_FLOW_DIAGRAM.md) | Team invitation system flow |
| 📄 [Team CRUD](TEAM_CRUD_DOCUMENTATION.md) | Complete team management API |
| 📄 [Task CRUD](TASK_CRUD_DOCUMENTATION.md) | Complete task management API |

**Testing Tools:**
- 📮 Import `Taskzilla_API_Complete.postman_collection.json` into Postman
- 🌍 Import `Taskzilla.postman_environment.json` for environment variables

---

## 📁 Project Structure

```
taskzilla/
├── app/
│   ├── AccessControl/
│   │   ├── Gates/              # Authorization gates
│   │   │   ├── TeamGates.php
│   │   │   └── TaskGates.php
│   │   └── Policies/           # Model policies
│   │
│   ├── Enums/                  # Application enums
│   │   ├── UserRole.php        # admin, lead, member
│   │   ├── TaskStatus.php      # pending, in_progress, completed
│   │   └── TaskPriority.php    # low, medium, high
│   │
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Auth/           # Authentication controllers
│   │   │   ├── Team/           # Team CRUD
│   │   │   ├── Task/           # Task CRUD
│   │   │   └── Invite/         # Invitation management
│   │   │
│   │   ├── Requests/           # Form request validation
│   │   │   ├── Team/
│   │   │   ├── Task/
│   │   │   └── Invite/
│   │   │
│   │   └── Middleware/         # Custom middleware
│   │
│   ├── Models/                 # Eloquent models
│   │   ├── User.php
│   │   ├── Team.php
│   │   ├── Task.php
│   │   ├── Invite.php
│   │   ├── TaskComment.php
│   │   └── TaskFile.php
│   │
│   ├── Services/               # Business logic layer
│   │   └── Module/
│   │       ├── Team/
│   │       │   └── TeamService.php
│   │       ├── Task/
│   │       │   └── TaskService.php
│   │       └── Invite/
│   │           └── InviteService.php
│   │
│   └── Traits/
│       └── HasUuid.php         # UUID generation trait
│
├── database/
│   ├── migrations/             # Database migrations
│   └── seeders/                # Database seeders
│
├── routes/
│   ├── api.php                 # API routes
│   └── auth.php                # Authentication routes
│
├── config/
│   ├── auth.php
│   ├── sanctum.php
│   └── ...
│
└── ...
```

---

## 🔐 Security

Taskzilla implements multiple security layers:

### 🛡️ Security Features

- ✅ **Token-Based Authentication** - Laravel Sanctum with secure token generation
- ✅ **UUID Route Binding** - Non-sequential, hard-to-guess identifiers
- ✅ **Gate Authorization** - Granular permission checks on every action
- ✅ **Input Validation** - Comprehensive FormRequest validation
- ✅ **CORS Protection** - Configurable CORS policies
- ✅ **Rate Limiting** - Protection against brute force attacks
- ✅ **Password Hashing** - Bcrypt hashing for user passwords
- ✅ **SQL Injection Protection** - Eloquent ORM with parameter binding
- ✅ **XSS Protection** - Laravel's built-in XSS prevention

### 🔒 Authorization Matrix

| Resource | Action | Admin | Lead | Member | Notes |
|----------|--------|-------|------|--------|-------|
| **Team** | Create | ✅ | ✅ | ✅ | Any user can create |
| | View | ✅ | ✅ | ✅ | If member of team |
| | Update | ✅ | ✅ | ❌ | Lead of team only |
| | Delete | ✅ | ❌ | ❌ | Admin only |
| **Task** | Create | ✅ | ✅ | ✅ | If team member |
| | View | ✅ | ✅ | ✅ | If team member or assignee |
| | Update | ✅ | ✅ | ❌ | Lead or creator only |
| | Delete | ✅ | ✅ | ❌ | Lead only |
| | Update Status | ✅ | ✅ | ✅ | If assigned to task |
| | Assign Users | ✅ | ✅ | ❌ | Lead or creator only |

---

## 🎨 Code Quality

### Architecture Principles

- ✨ **Clean Architecture** - Separation of concerns (Controller → Service → Model)
- 🏗️ **SOLID Principles** - Single responsibility, dependency injection
- 🔄 **Repository Pattern** - Service layer abstracts database operations
- 🎯 **RESTful Design** - Predictable, resource-based API structure
- 📝 **PSR Standards** - Following PHP-FIG standards
- 🧪 **Testable Code** - Dependency injection for easy mocking

### Code Features

```php
// ✅ Clean Controller
public function createTask(TaskRequest $request): JsonResponse
{
    try {
        $task = $this->taskService->createTask(
            $request->validated(),
            $request->user()
        );
        return response()->json(['success' => true, 'data' => $task], 201);
    } catch (\Throwable $th) {
        Log::error('Failed to create task', [...]);
        return response()->json(['success' => false, ...], 500);
    }
}

// ✅ Service Layer with Transactions
public function createTask(array $data, User $user): Task
{
    return DB::transaction(function () use ($data, $user) {
        $task = Task::create([...]);
        $this->assignUsers($task, $data['assignee_ids']);
        return $task->load([...]);
    });
}

// ✅ Gate Authorization
Gate::define('update-task', function (User $user, Task $task) {
    return $user->role === UserRole::ADMIN
        || $user->id === $task->team->lead_id
        || $user->id === $task->assigned_by;
});
```

---

## 🧪 Testing

```bash
# Run all tests
php artisan test

# Run specific test suite
php artisan test --testsuite=Feature

# Run with coverage
php artisan test --coverage
```

---

## 📚 Documentation Index

### 🔄 Application Flows & Diagrams

| Document | Description | Best For |
|----------|-------------|----------|
| [Application Flow](Flows/APPLICATION_FLOW.md) | Complete system overview with visual diagrams | Understanding the big picture |
| [Invitation Flow](Flows/INVITATION_FLOW_DIAGRAM.md) | Team invitation system workflow | Understanding team collaboration |

### 📖 API Documentation

| Document | Description | Best For |
|----------|-------------|----------|
| [Team CRUD](TEAM_CRUD_DOCUMENTATION.md) | Team management endpoints | Building team features |
| [Task CRUD](TASK_CRUD_DOCUMENTATION.md) | Task management endpoints | Building task features |

### 🧪 Testing & Tools

| File | Description | Usage |
|------|-------------|-------|
| `Taskzilla_API_Complete.postman_collection.json` | Complete Postman collection (24 endpoints) | Import to Postman for testing |
| `Taskzilla.postman_environment.json` | Environment variables | Import to Postman |

### 🗄️ Database Documentation

| Document | Description | Best For |
|----------|-------------|----------|
| `database_schema.dbml` | Complete database schema in DBML format | Visualizing on dbdiagram.io |

### 💡 Additional Resources

| Document | Description |
|----------|-------------|
| `README.md` | This file - project overview |
| `TASK_CRUD_DOCUMENTATION.md` | Detailed task API documentation |
| `TEAM_CRUD_DOCUMENTATION.md` | Detailed team API documentation |

**Quick Links:**
- 🎯 [Get Started](#-installation) - Install and run
- 🔐 [Security](#-security) - Security features
- 🏗️ [Architecture](#-architecture) - System design
- 📊 [Database Schema](#-database-schema) - Data structure

---

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

### Development Setup

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

### Coding Standards

- Follow PSR-12 coding standards
- Write meaningful commit messages
- Add tests for new features
- Update documentation as needed

---

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

---

## 🙏 Acknowledgments

- Built with [Laravel](https://laravel.com) - The PHP Framework for Web Artisans
- Authentication powered by [Laravel Sanctum](https://laravel.com/docs/sanctum)
- Database: [PostgreSQL](https://postgresql.org) - The World's Most Advanced Open Source Relational Database

---

## 📞 Support

- 📧 Email: support@taskzilla.com
- 📖 Documentation: [See docs folder](docs/)
- 🐛 Issues: [GitHub Issues](https://github.com/yourusername/taskzilla/issues)

---

<div align="center">

**Made with ❤️ and ☕ by the Taskzilla Team**

⭐ Star us on GitHub — it motivates us a lot!

[⬆ Back to Top](#-taskzilla)

</div>
