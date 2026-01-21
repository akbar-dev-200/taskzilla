<?php

namespace App\AccessControl\Gates;

use App\Enums\UserRole;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

class TaskGates
{
    public static function register(): void
    {
        // User can create task if they are member of the team
        Gate::define('create-task', function (User $user, string|int $teamId) {
            // Support both UUID (string) and ID (int) for backward compatibility
            $column = is_int($teamId) ? 'teams.id' : 'teams.uuid';
            return $user->teams()->where($column, $teamId)->exists();
        });

        // User can view task if they are member of the team or assigned to the task
        Gate::define('view-task', function (User $user, Task $task) {
            return $user->teams()->where('teams.id', $task->team_id)->exists()
                || $task->assignees()->where('users.id', $user->id)->exists();
        });

        // User can update task if they are team lead, admin, or task creator
        Gate::define('update-task', function (User $user, Task $task) {
            $team = $task->team;
            return $user->role === UserRole::ADMIN
                || $user->id === $team->lead_id
                || $user->id === $task->assigned_by;
        });

        // User can delete task if they are admin or team lead
        Gate::define('delete-task', function (User $user, Task $task) {
            $team = $task->team;
            return $user->role === UserRole::ADMIN
                || $user->id === $team->lead_id;
        });

        // User can update task status if they are a team member, assigned to it, team lead, or admin
        Gate::define('update-task-status', function (User $user, Task $task) {
            $team = $task->team;
            return $user->role === UserRole::ADMIN
                || $user->id === $team->lead_id
                || $task->assignees()->where('users.id', $user->id)->exists()
                || $user->teams()->where('teams.id', $task->team_id)->exists(); // Any team member
        });

        // User can assign/unassign users if they are team lead, admin, or task creator
        Gate::define('manage-task-assignees', function (User $user, Task $task) {
            $team = $task->team;
            return $user->role === UserRole::ADMIN
                || $user->id === $team->lead_id
                || $user->id === $task->assigned_by;
        });
    }
}
