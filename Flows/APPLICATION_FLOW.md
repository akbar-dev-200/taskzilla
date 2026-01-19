# 🦖 Taskzilla Application Flow

> **High-Level Overview: How the entire application works from start to finish**

---

## 🎯 Application Overview

Taskzilla is a **team-based task management system** where:
1. Users register and create teams
2. Team leads invite members via email
3. Members collaborate on tasks
4. Tasks can be assigned to multiple users
5. Everyone tracks progress with real-time updates

---

## 🔄 Complete User Journey

```
┌──────────────────────────────────────────────────────────────────┐
│                    TASKZILLA USER JOURNEY                         │
└──────────────────────────────────────────────────────────────────┘

START
  │
  ├─► 1️⃣ USER REGISTRATION/LOGIN
  │      │
  │      ├─ POST /api/register → Creates account (gets token)
  │      └─ POST /api/login → Gets authentication token
  │         │
  │         └─► Token stored in client (localStorage/mobile app)
  │
  ├─► 2️⃣ CREATE/JOIN TEAMS
  │      │
  │      ├─ POST /api/teams → Create new team (becomes team lead)
  │      │    └─► Auto-added as member with LEAD role
  │      │
  │      └─ POST /api/invites/accept → Join via invitation
  │           └─► Added to team with assigned role
  │
  ├─► 3️⃣ INVITE TEAM MEMBERS (Team Lead)
  │      │
  │      ├─ POST /api/invites → Send email invitations
  │      │    └─► Emails sent with unique tokens
  │      │
  │      └─ Invited users receive email → Accept → Join team
  │
  ├─► 4️⃣ MANAGE TASKS
  │      │
  │      ├─ POST /api/tasks → Create task in team
  │      │    └─► Can assign multiple users at creation
  │      │
  │      ├─ GET /api/tasks/my-tasks → View assigned tasks
  │      │
  │      ├─ PATCH /api/tasks/{uuid}/status → Update progress
  │      │    └─► pending → in_progress → completed
  │      │
  │      ├─ POST /api/tasks/{uuid}/assign → Add more assignees
  │      │
  │      └─ POST /api/tasks/{uuid}/remove-assignees → Remove users
  │
  ├─► 5️⃣ COLLABORATE
  │      │
  │      ├─ View team dashboard → See all tasks & members
  │      ├─ Add comments to tasks → Discuss progress
  │      ├─ Upload files to tasks → Share resources
  │      └─ Track statistics → Monitor team progress
  │
  └─► 6️⃣ LOGOUT
         │
         └─ POST /api/logout → Revoke all tokens
END
```

---

## 🏗️ System Architecture Flow

```
┌──────────────────────────────────────────────────────────────────┐
│              REQUEST → RESPONSE FLOW                              │
└──────────────────────────────────────────────────────────────────┘

CLIENT (Postman/Mobile/Web)
   │
   │ HTTP Request with Bearer Token
   │ Authorization: Bearer 1|abc123...
   ▼
┌──────────────────────────────────────┐
│         LARAVEL ROUTING              │
│          (routes/api.php)            │
│                                      │
│  • Matches URL to controller        │
│  • Applies middleware (auth:sanctum)│
└──────────────────┬───────────────────┘
                   │
                   ▼
┌──────────────────────────────────────┐
│      AUTHENTICATION CHECK            │
│      (Laravel Sanctum)               │
│                                      │
│  • Validates Bearer token            │
│  • Loads user from token             │
│  • Sets $request->user()             │
└──────────────────┬───────────────────┘
                   │
                   ▼
┌──────────────────────────────────────┐
│        FORM REQUEST                  │
│    (e.g., TeamRequest)               │
│                                      │
│  Step 1: authorize()                 │
│    • Checks Gates                    │
│    • Returns 403 if denied           │
│                                      │
│  Step 2: rules()                     │
│    • Validates input data            │
│    • Returns 422 if invalid          │
└──────────────────┬───────────────────┘
                   │
                   ▼
┌──────────────────────────────────────┐
│          CONTROLLER                  │
│     (e.g., TeamController)           │
│                                      │
│  • Receives validated data           │
│  • Calls service layer               │
│  • Returns JSON response             │
└──────────────────┬───────────────────┘
                   │
                   ▼
┌──────────────────────────────────────┐
│          SERVICE LAYER               │
│     (e.g., TeamService)              │
│                                      │
│  • Business logic                    │
│  • Database queries                  │
│  • Transactions                      │
│  • Data transformation               │
└──────────────────┬───────────────────┘
                   │
                   ▼
┌──────────────────────────────────────┐
│           ELOQUENT ORM               │
│         (Laravel Models)             │
│                                      │
│  • Query building                    │
│  • Relationship loading              │
│  • Data persistence                  │
└──────────────────┬───────────────────┘
                   │
                   ▼
┌──────────────────────────────────────┐
│          DATABASE                    │
│        (PostgreSQL)                  │
│                                      │
│  • Data storage                      │
│  • Referential integrity             │
│  • Constraints & indexes             │
└──────────────────┬───────────────────┘
                   │
                   │ Return data
                   ▼
              JSON RESPONSE
                   │
                   │ HTTP 200/201/4xx/5xx
                   ▼
                CLIENT
```

---

## 🎭 Main Feature Flows

### Flow 1: User Onboarding

```
┌─────────────────────────────────────────────────────────────┐
│                   USER ONBOARDING                            │
└─────────────────────────────────────────────────────────────┘

New User
   │
   │ POST /api/register
   │ {name, email, password}
   ▼
┌──────────────────────┐
│ User Created         │
│ • UUID generated     │
│ • Password hashed    │
│ • Role: ADMIN        │
│ • Token created      │
└──────┬───────────────┘
       │
       │ Returns: {user, token}
       ▼
┌──────────────────────┐
│ Client Stores Token  │
│ • localStorage (web) │
│ • SecureStore (app)  │
└──────┬───────────────┘
       │
       ▼
   AUTHENTICATED
```

---

### Flow 2: Team Creation & Management

```
┌─────────────────────────────────────────────────────────────┐
│                   TEAM LIFECYCLE                             │
└─────────────────────────────────────────────────────────────┘

Authenticated User
   │
   │ POST /api/teams
   │ {name: "Development Team"}
   ▼
┌──────────────────────────────────┐
│ TeamService.createTeam()         │
│                                  │
│ Transaction {                    │
│   1. Create team                 │
│      - lead_id = current user    │
│      - created_by = current user │
│                                  │
│   2. Add creator to team_user    │
│      - role = LEAD               │
│      - joined_at = now()         │
│ }                                │
└──────┬───────────────────────────┘
       │
       │ Team Created ✅
       ▼
┌──────────────────────────────────┐
│ User Can Now:                    │
│ • Invite members                 │
│ • Create tasks                   │
│ • Manage team                    │
└──────────────────────────────────┘
```

---

### Flow 3: Team Invitation System

```
┌─────────────────────────────────────────────────────────────┐
│              INVITATION WORKFLOW                             │
└─────────────────────────────────────────────────────────────┘

Team Lead
   │
   │ POST /api/invites
   │ {team_id, emails: [...], role}
   ▼
┌──────────────────────────────────┐
│ For Each Email:                  │
│                                  │
│ 1. Check not already member      │
│ 2. Check no pending invite       │
│ 3. Create invite record          │
│    - Generate unique token       │
│    - Set expiry (7 days)         │
│    - Status: pending             │
│ 4. Queue email job               │
└──────┬───────────────────────────┘
       │
       │ Email Sent 📧
       ▼
┌──────────────────────────────────┐
│ Invitee Inbox                    │
│ • Invitation email               │
│ • Accept link with token         │
│ • Expires in 7 days              │
└──────┬───────────────────────────┘
       │
       │ Clicks Accept
       ▼
┌──────────────────────────────────┐
│ POST /api/invites/accept         │
│ {token}                          │
│                                  │
│ Validation:                      │
│ ✓ Token valid                    │
│ ✓ Not expired                    │
│ ✓ Status = pending               │
│ ✓ User logged in                 │
└──────┬───────────────────────────┘
       │
       │ Transaction {
       │   1. Add to team_user
       │   2. Update invite status
       │ }
       ▼
┌──────────────────────────────────┐
│ User Joined Team ✅              │
│ • Can now access team            │
│ • Can view/create tasks          │
│ • Assigned role from invite      │
└──────────────────────────────────┘
```

---

### Flow 4: Task Creation & Assignment

```
┌─────────────────────────────────────────────────────────────┐
│               TASK WORKFLOW                                  │
└─────────────────────────────────────────────────────────────┘

Team Member
   │
   │ POST /api/tasks
   │ {
   │   title, description,
   │   team_id, priority,
   │   assignee_ids: [2,3,4]
   │ }
   ▼
┌──────────────────────────────────┐
│ TaskService.createTask()         │
│                                  │
│ Transaction {                    │
│   1. Create task                 │
│      - assigned_by = creator     │
│      - status = pending          │
│      - UUID generated            │
│                                  │
│   2. Assign users                │
│      For each user:              │
│      - Add to task_assignees     │
│      - assigned_at = now()       │
│ }                                │
└──────┬───────────────────────────┘
       │
       │ Task Created ✅
       ▼
┌──────────────────────────────────┐
│ Assignees Can:                   │
│ • View task                      │
│ • Update status                  │
│ • Add comments                   │
│ • Upload files                   │
└──────────────────────────────────┘
```

---

### Flow 5: Task Status Updates

```
┌─────────────────────────────────────────────────────────────┐
│             TASK STATUS WORKFLOW                             │
└─────────────────────────────────────────────────────────────┘

                     ┌──────────┐
                     │ PENDING  │ ← Created
                     └─────┬────┘
                           │
        Assignee starts    │ PATCH /api/tasks/{uuid}/status
              work         │ {status: "in_progress"}
                           │
                           ▼
                     ┌──────────────┐
                     │ IN_PROGRESS  │
                     └─────┬────────┘
                           │
        Assignee           │ PATCH /api/tasks/{uuid}/status
       completes           │ {status: "completed"}
                           │
                           ▼
                     ┌──────────┐
                     │COMPLETED │ ← Done ✅
                     └──────────┘
                     
Permissions:
• Assignees can update status
• Team lead can update status
• Admin can update status
```

---

## 📊 Data Flow Diagram

```
┌──────────────────────────────────────────────────────────────────┐
│                      DATA RELATIONSHIPS                           │
└──────────────────────────────────────────────────────────────────┘

┌─────────┐
│  USER   │ ────────────────────┐
└────┬────┘                     │
     │                          │
     │ creates                  │ belongs to (M:N)
     │                          │
     ▼                          ▼
┌─────────┐              ┌─────────────┐
│  TEAM   │◄─────────────│  TEAM_USER  │ (pivot)
└────┬────┘   has members└─────────────┘
     │                          │
     │ contains                 │ has role
     │                          │ (admin/lead/member)
     ▼                          │
┌─────────┐                     │
│  TASK   │◄────────────────────┘
└────┬────┘   assigned to (M:N)
     │
     │ has
     │
     ├──► TASK_ASSIGNEES (pivot) → Multiple users
     ├──► TASK_COMMENTS → Discussion
     └──► TASK_FILES → Attachments
```

---

## 🎭 Actor & Permission Flow

```
┌──────────────────────────────────────────────────────────────────┐
│                    WHO CAN DO WHAT?                               │
└──────────────────────────────────────────────────────────────────┘

┌──────────────┐
│ SYSTEM ADMIN │ (User.role = admin)
└──────┬───────┘
       │ Can:
       ├─► Create teams
       ├─► Update ANY team
       ├─► Delete ANY team
       ├─► Manage ANY task
       └─► Full system access

┌──────────────┐
│  TEAM LEAD   │ (team_user.role = lead)
└──────┬───────┘
       │ Can (in THEIR team):
       ├─► Invite members
       ├─► Update team
       ├─► Create tasks
       ├─► Assign tasks
       ├─► Delete tasks
       └─► Manage team settings

┌──────────────┐
│ TEAM MEMBER  │ (team_user.role = member)
└──────┬───────┘
       │ Can (in THEIR team):
       ├─► View team
       ├─► Create tasks
       ├─► View tasks
       └─► Update assigned task status

┌──────────────┐
│  TASK OWNER  │ (task.assigned_by = user)
└──────┬───────┘
       │ Can (for THEIR tasks):
       ├─► Update task
       ├─► Assign users
       ├─► Remove assignees
       └─► Delete task

┌──────────────┐
│   ASSIGNEE   │ (in task_assignees)
└──────┬───────┘
       │ Can (for assigned tasks):
       ├─► View task
       ├─► Update status
       ├─► Add comments
       └─► Upload files
```

---

## 🔐 Authentication Flow

```
┌──────────────────────────────────────────────────────────────────┐
│                  AUTHENTICATION SYSTEM                            │
└──────────────────────────────────────────────────────────────────┘

Registration/Login
       │
       │ Credentials validated
       ▼
┌────────────────────────────┐
│ Laravel Sanctum            │
│ creates API token          │
│                            │
│ $user->createToken()       │
│   │                        │
│   └─► Stores in:           │
│       personal_access_     │
│       tokens table         │
└────────┬───────────────────┘
         │
         │ Returns: 1|abc123xyz...
         ▼
┌────────────────────────────┐
│ Client Stores Token        │
│ • Web: localStorage        │
│ • Mobile: SecureStore      │
└────────┬───────────────────┘
         │
         │ Every API request
         ▼
┌────────────────────────────┐
│ Authorization Header:      │
│ Bearer 1|abc123xyz...      │
└────────┬───────────────────┘
         │
         │ Sanctum validates
         ▼
┌────────────────────────────┐
│ User Authenticated ✅      │
│ Request proceeds           │
└────────────────────────────┘
```

---

## 📋 Task Management Flow

```
┌──────────────────────────────────────────────────────────────────┐
│              COMPLETE TASK LIFECYCLE                              │
└──────────────────────────────────────────────────────────────────┘

CREATE
  │
  │ Team member creates task
  │ POST /api/tasks
  ▼
┌──────────────────────────┐
│ Task Created             │
│ • Status: pending        │
│ • Assigned to users      │
│ • UUID generated         │
└──────┬───────────────────┘
       │
       ▼
ASSIGN/REASSIGN
  │
  │ Team lead manages assignees
  │ POST /api/tasks/{uuid}/assign
  │ POST /api/tasks/{uuid}/remove-assignees
  ▼
┌──────────────────────────┐
│ Assignees Updated        │
│ • Multiple users can     │
│   work on same task      │
└──────┬───────────────────┘
       │
       ▼
WORK ON TASK
  │
  │ Assignees collaborate
  │ • Update status
  │ • Add comments
  │ • Upload files
  ▼
┌──────────────────────────┐
│ Status: in_progress      │
│ • Team tracks progress   │
│ • Comments added         │
│ • Files attached         │
└──────┬───────────────────┘
       │
       ▼
COMPLETE
  │
  │ Assignee marks done
  │ PATCH /api/tasks/{uuid}/status
  │ {status: "completed"}
  ▼
┌──────────────────────────┐
│ Task Completed ✅        │
│ • Status: completed      │
│ • Visible in stats       │
│ • Archived/filtered      │
└──────────────────────────┘
```

---

## 🔄 Multi-User Collaboration Flow

```
┌──────────────────────────────────────────────────────────────────┐
│           TEAM COLLABORATION SCENARIO                             │
└──────────────────────────────────────────────────────────────────┘

John (Team Lead)
   │
   │ 1. Creates "Development Team"
   ▼
Team Created
   │
   │ 2. Invites Alice & Bob
   │ POST /api/invites
   ▼
Invitations Sent 📧
   │
   ├──► Alice receives email → Accepts → Joins as MEMBER
   └──► Bob receives email → Accepts → Joins as MEMBER
   │
   │ Team now has: John (LEAD), Alice (MEMBER), Bob (MEMBER)
   ▼
John creates task
   │ POST /api/tasks
   │ {title: "Build API", assignee_ids: [Alice.id, Bob.id]}
   ▼
┌────────────────────────────────┐
│ Task Assigned to Alice & Bob   │
└────┬───────────────────────────┘
     │
     ├──► Alice: GET /api/tasks/my-tasks → Sees task
     │          PATCH /api/tasks/{uuid}/status → in_progress
     │
     └──► Bob: GET /api/tasks/my-tasks → Sees task
              POST /api/tasks/{uuid}/comments → Adds comment
              PATCH /api/tasks/{uuid}/status → completed
```

---

## 🎯 Key Flows Summary

### 1. **Authentication Flow**
```
Register → Get Token → Store Token → Use Token → Logout → Token Revoked
```

### 2. **Team Flow**
```
Create Team → Invite Members → Members Accept → Team Active → Manage Team
```

### 3. **Task Flow**
```
Create Task → Assign Users → Update Status → Add Comments → Complete
```

### 4. **Invitation Flow**
```
Send Invite → Email Sent → User Accepts → Added to Team → Can Collaborate
```

### 5. **Authorization Flow**
```
Request → Check Auth → Check Gates → Check Validation → Execute → Response
```

---

## 📊 Database Transaction Flows

### Critical Operations with Transactions

#### **Create Team:**
```
BEGIN TRANSACTION
  1. INSERT into teams
  2. INSERT into team_user (add creator)
COMMIT
```

#### **Accept Invitation:**
```
BEGIN TRANSACTION
  1. INSERT into team_user (add invitee)
  2. UPDATE invites (set accepted)
COMMIT
```

#### **Create Task with Assignees:**
```
BEGIN TRANSACTION
  1. INSERT into tasks
  2. INSERT into task_assignees (for each user)
COMMIT
```

#### **Delete Team:**
```
BEGIN TRANSACTION
  1. DELETE from team_user (all members)
  2. DELETE from teams
  3. CASCADE: tasks, invites deleted
COMMIT
```

#### **Delete Task:**
```
BEGIN TRANSACTION
  1. DELETE from task_assignees (all assignees)
  2. DELETE from tasks
  3. CASCADE: comments, files deleted
COMMIT
```

---

## 🚀 API Request Flow Pattern

Every API request follows this pattern:

```
1. CLIENT SENDS REQUEST
   └─► Includes: Bearer Token, JSON Body

2. LARAVEL RECEIVES
   └─► Routes to controller

3. AUTHENTICATION
   └─► Sanctum validates token

4. AUTHORIZATION
   └─► Gates check permissions

5. VALIDATION
   └─► FormRequest validates input

6. BUSINESS LOGIC
   └─► Service layer processes

7. DATABASE
   └─► Models interact with DB

8. RESPONSE
   └─► Clean JSON returned

9. CLIENT RECEIVES
   └─► Updates UI
```

---

## 🎨 Error Handling Flow

```
┌──────────────────────────────────────────────────────────────────┐
│                    ERROR SCENARIOS                                │
└──────────────────────────────────────────────────────────────────┘

Request
   │
   ├─► No/Invalid Token → 401 Unauthorized
   │
   ├─► Validation Fails → 422 Validation Error
   │                       {errors: {field: ["message"]}}
   │
   ├─► No Permission → 403 Forbidden
   │                    "You cannot perform this action"
   │
   ├─► Not Found → 404 Not Found
   │                "Team/Task not found"
   │
   ├─► Server Error → 500 Internal Error
   │                   {success: false, message: "..."}
   │
   └─► Success → 200/201 OK
                  {success: true, data: {...}}
```

---

## 🎯 Feature Interaction Flow

```
┌──────────────────────────────────────────────────────────────────┐
│            HOW FEATURES WORK TOGETHER                             │
└──────────────────────────────────────────────────────────────────┘

User Registers
   │
   ├─► Creates Team 1 (becomes Lead)
   │     │
   │     ├─► Invites Members → Members Join
   │     │     │
   │     │     └─► Members Create Tasks → Tasks Assigned
   │     │           │
   │     │           └─► Assignees Update Status
   │     │                 │
   │     │                 └─► Add Comments & Files
   │     │
   │     └─► Lead Views Statistics → Monitors Progress
   │
   ├─► Joins Team 2 (via invitation - becomes Member)
   │     │
   │     └─► Gets assigned tasks → Works on tasks
   │
   └─► Views "My Tasks" across all teams
         └─► Filters by status/priority
```

---

## 🌟 Real-World Usage Example

```
════════════════════════════════════════════════════════════════
         EXAMPLE: Software Development Team
════════════════════════════════════════════════════════════════

DAY 1: Setup
────────────
John (CTO) → Registers → Creates "Engineering Team"
           → Invites: alice@dev.com, bob@dev.com

DAY 2: Team Forms
─────────────────
Alice → Receives email → Accepts → Joins as Member
Bob → Receives email → Accepts → Joins as Member

DAY 3: Work Begins
──────────────────
John → Creates Task: "Build Authentication API"
    → Assigns to: Alice, Bob
    → Priority: High
    → Due: 7 days

Alice → Views "My Tasks" → Sees new task
     → Updates status to "in_progress"
     → Adds comment: "Starting with JWT"

Bob → Views task → Adds comment: "I'll handle the database"
   → Uploads file: "auth-schema.pdf"

DAY 5: Progress Update
──────────────────────
Alice → Updates status to "completed"
John → Views team statistics
    → Sees: 1 completed, 0 pending

DAY 6: New Sprint
─────────────────
John → Creates 5 new tasks
    → Assigns to team members
    → Team collaborates
```

---

## 💡 Key Concepts

### 🔑 Authentication
- **Stateless** - Token-based, no sessions
- **Persistent** - Tokens never expire (configurable)
- **Secure** - Tokens hashed in database
- **Multi-device** - One user, many tokens

### 👥 Teams
- **Multi-membership** - Users join multiple teams
- **Role-based** - Different roles per team
- **Isolated** - Teams can't see each other's data
- **Hierarchical** - Lead → Members structure

### 📋 Tasks
- **Multi-assignment** - One task, many assignees
- **Status workflow** - Pending → In Progress → Completed
- **Team-scoped** - Tasks belong to one team
- **Rich metadata** - Comments, files, priority, due dates

### 📧 Invitations
- **Email-based** - Sent to email addresses
- **Token-secured** - Unique tokens for security
- **Expirable** - 7-day expiration (configurable)
- **Trackable** - Status tracking (pending/accepted/revoked)

---

## 🔄 System Workflows

### Daily User Workflow

```
Morning:
  1. Login → Get token
  2. GET /api/tasks/my-tasks → See today's tasks
  3. Filter by priority=high → Focus on urgent items

During Day:
  4. For each task:
     - View details
     - Update status
     - Add comments
     - Upload files

Evening:
  5. GET /api/tasks/team/{id}/statistics → Review progress
  6. Create tasks for tomorrow
  7. Assign to team members
```

### Team Lead Workflow

```
Weekly:
  1. GET /api/teams → View all teams
  2. For each team:
     - GET /api/tasks/team/{id}/statistics
     - Review completed vs pending
     - Check overdue tasks
  
As Needed:
  3. POST /api/invites → Invite new members
  4. POST /api/tasks → Create new tasks
  5. POST /api/tasks/{uuid}/assign → Reassign tasks
  6. PUT /api/teams/{uuid} → Update team settings
```

---

## 🎯 Summary: The Complete Flow

```
┌─────────────────────────────────────────────────────────────┐
│         TASKZILLA IN 10 STEPS                                │
└─────────────────────────────────────────────────────────────┘

1️⃣  User registers → Gets auth token
2️⃣  User creates team → Becomes team lead
3️⃣  Lead invites members → Sends email invitations
4️⃣  Members accept → Join team with assigned roles
5️⃣  Lead creates tasks → Assigns to team members
6️⃣  Assignees view tasks → See "My Tasks" list
7️⃣  Assignees work → Update status, add comments
8️⃣  Team collaborates → Files, discussions, updates
9️⃣  Tasks complete → Statistics updated
🔟 Rinse & repeat → Continuous productivity!
```

---

## 📚 Technical Stack Flow

```
┌──────────────────────────────────────────────────────────────────┐
│              TECHNOLOGY FLOW                                      │
└──────────────────────────────────────────────────────────────────┘

Request
  │
  ├─► Laravel Router → Maps URL to controller
  ├─► Sanctum Middleware → Validates token
  ├─► FormRequest → Validates & authorizes
  ├─► Controller → Handles HTTP
  ├─► Service → Business logic
  ├─► Model (Eloquent) → Database operations
  ├─► PostgreSQL → Data persistence
  │
  └─► Response (JSON) → Client receives
```

---

## ✅ What Makes Taskzilla Special?

1. **Clean Architecture** - Controller → Service → Model separation
2. **Security First** - UUID routes, Gate authorization, token auth
3. **Scalable Design** - Multi-team, multi-user, multi-assignment
4. **Professional Code** - PSR standards, type hints, documentation
5. **Production Ready** - Error handling, logging, transactions
6. **Developer Friendly** - RESTful API, consistent responses
7. **Well Documented** - Every endpoint, flow, and feature explained

---

<div align="center">

**That's the complete high-level flow of Taskzilla!** 🦖

Every feature works together to create a powerful, collaborative task management system.

**From zero to fully functional team productivity in minutes!** 🚀

</div>
