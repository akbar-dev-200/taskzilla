<?php

namespace App\Services\Module\Task;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TaskService
{
    /**
     * Get filtered and paginated tasks for a team
     *
     * @param int $teamId
     * @param array $filters
     * @param int|null $perPage
     * @return LengthAwarePaginator
     */
    public function getTeamTasks(int $teamId, array $filters = [], ?int $perPage = 10): LengthAwarePaginator
    {
        $query = Task::query()
            ->where('team_id', $teamId)
            ->with([
                'assignedBy:id,name,email',
                'assignees:id,name,email',
                'team:id,name',
            ])
            ->withCount(['comments', 'files', 'assignees']);

        // Apply filters
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }

        if (isset($filters['assigned_to'])) {
            $query->whereHas('assignees', function ($q) use ($filters) {
                $q->where('users.id', $filters['assigned_to']);
            });
        }

        if (isset($filters['assigned_by'])) {
            $query->where('assigned_by', $filters['assigned_by']);
        }

        if (isset($filters['overdue']) && $filters['overdue']) {
            $query->where('due_date', '<', now())
                ->where('status', '!=', TaskStatus::COMPLETED);
        }

        if (isset($filters['due_soon']) && $filters['due_soon']) {
            $query->whereBetween('due_date', [now(), now()->addDays(7)])
                ->where('status', '!=', TaskStatus::COMPLETED);
        }

        // Sort
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        return $query->latest()->paginate($perPage);
    }

    /**
     * Get tasks assigned to a specific user
     *
     * @param User $user
     * @param array $filters
     * @param int|null $perPage
     * @return LengthAwarePaginator
     */
    public function getUserTasks(User $user, array $filters = [], ?int $perPage = 10): LengthAwarePaginator
    {
        $query = Task::query()
            ->whereHas('assignees', function ($q) use ($user) {
                $q->where('users.id', $user->id);
            })
            ->with([
                'assignedBy:id,name,email',
                'assignees:id,name,email',
                'team:id,name',
            ])
            ->withCount(['comments', 'files', 'assignees']);

        // Apply filters (reuse same filter logic)
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }

        if (isset($filters['team_id'])) {
            $query->where('team_id', $filters['team_id']);
        }

        return $query->latest()->paginate($perPage);
    }

    /**
     * Create a new task
     *
     * @param array $data
     * @param User $user
     * @return Task
     */
    public function createTask(array $data, User $user): Task
    {
        return DB::transaction(function () use ($data, $user) {
            // Create the task
            $task = Task::create([
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'status' => $data['status'] ?? TaskStatus::PENDING,
                'priority' => $data['priority'] ?? TaskPriority::MEDIUM,
                'due_date' => $data['due_date'] ?? null,
                'team_id' => $data['team_id'],
                'assigned_by' => $user->id,
            ]);

            // Assign users if provided
            if (!empty($data['assignee_ids'])) {
                $this->assignUsers($task, $data['assignee_ids']);
            }

            // Load relationships for response
            $task->load([
                'assignedBy:id,name,email',
                'assignees:id,name,email',
                'team:id,name',
            ]);

            return $task;
        });
    }

    /**
     * Get task details with all relationships
     *
     * @param Task $task
     * @return Task
     */
    public function getTaskDetails(Task $task): Task
    {
        return $task->load([
            'assignedBy:id,name,email',
            'assignees' => function ($query) {
                $query->select('users.id', 'users.name', 'users.email')
                    ->withPivot(['uuid', 'assigned_at']);
            },
            'team:id,name,uuid',
            'comments' => function ($query) {
                $query->with('user:id,name,email')
                    ->latest()
                    ->limit(10);
            },
            'files' => function ($query) {
                $query->latest()->limit(10);
            }
        ])
        ->loadCount(['comments', 'files', 'assignees']);
    }

    /**
     * Update task details
     *
     * @param Task $task
     * @param array $data
     * @return Task
     */
    public function updateTask(Task $task, array $data): Task
    {
        $task->update($data);
        
        // Reload relationships
        $task->load([
            'assignedBy:id,name,email',
            'assignees:id,name,email',
            'team:id,name',
        ]);

        return $task;
    }

    /**
     * Update task status
     *
     * @param Task $task
     * @param TaskStatus $status
     * @return Task
     */
    public function updateTaskStatus(Task $task, TaskStatus $status): Task
    {
        $task->update(['status' => $status]);
        
        $task->load([
            'assignedBy:id,name,email',
            'assignees:id,name,email',
            'team:id,name',
        ]);

        return $task;
    }

    /**
     * Delete a task
     *
     * @param Task $task
     * @return bool
     */
    public function deleteTask(Task $task): bool
    {
        return DB::transaction(function () use ($task) {
            // Detach all assignees
            $task->assignees()->detach();
            
            // Delete the task (comments and files will cascade)
            return $task->delete();
        });
    }

    /**
     * Assign users to a task
     *
     * @param Task $task
     * @param array $userIds
     * @return void
     */
    public function assignUsers(Task $task, array $userIds): void
    {
        $syncData = [];
        foreach ($userIds as $userId) {
            $syncData[$userId] = [
                'uuid' => Str::uuid()->toString(),
                'assigned_at' => now(),
            ];
        }

        $task->assignees()->syncWithoutDetaching($syncData);
    }

    /**
     * Remove users from a task
     *
     * @param Task $task
     * @param array $userIds
     * @return void
     */
    public function removeUsers(Task $task, array $userIds): void
    {
        $task->assignees()->detach($userIds);
    }

    /**
     * Replace all assignees with new ones
     *
     * @param Task $task
     * @param array $userIds
     * @return void
     */
    public function syncAssignees(Task $task, array $userIds): void
    {
        $syncData = [];
        foreach ($userIds as $userId) {
            $syncData[$userId] = [
                'uuid' => Str::uuid()->toString(),
                'assigned_at' => now(),
            ];
        }

        $task->assignees()->sync($syncData);
    }

    /**
     * Get task statistics for a team
     *
     * @param int $teamId
     * @return array
     */
    public function getTaskStatistics(int $teamId): array
    {
        $tasks = Task::where('team_id', $teamId);

        return [
            'total' => $tasks->count(),
            'pending' => $tasks->clone()->where('status', TaskStatus::PENDING)->count(),
            'in_progress' => $tasks->clone()->where('status', TaskStatus::IN_PROGRESS)->count(),
            'completed' => $tasks->clone()->where('status', TaskStatus::COMPLETED)->count(),
            'overdue' => $tasks->clone()
                ->where('due_date', '<', now())
                ->where('status', '!=', TaskStatus::COMPLETED)
                ->count(),
            'high_priority' => $tasks->clone()->where('priority', TaskPriority::HIGH)->count(),
        ];
    }
}
