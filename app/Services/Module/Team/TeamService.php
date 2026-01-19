<?php

namespace App\Services\Module\Team;

use App\Enums\UserRole;
use App\Models\Team;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TeamService
{
    /**
     * Get all teams the user belongs to
     *
     * @param User $user
     * @param int|null $perPage
     * @return LengthAwarePaginator
     */
    public function getUserTeams(User $user, ?int $perPage = 10): LengthAwarePaginator
    {
        return Team::query()
            ->whereHas('members', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->with([
                'lead:id,name,email',
                'creator:id,name,email',
                'members' => function ($query) {
                    $query->select('users.id', 'users.name', 'users.email')
                        ->withPivot(['role', 'joined_at']);
                }
            ])
            ->withCount(['members', 'tasks'])
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Create a new team
     *
     * @param array $data
     * @param User $user
     * @return Team
     */
    public function createTeam(array $data, User $user): Team
    {
        return DB::transaction(function () use ($data, $user) {
            // Create the team
            $team = Team::create([
                'name' => $data['name'],
                'lead_id' => $user->id,
                'created_by' => $user->id,
            ]);

            // Attach the creator as a member with LEAD role
            $team->members()->attach($user->id, [
                'uuid' => Str::uuid()->toString(),
                'role' => UserRole::LEAD,
                'joined_at' => now(),
            ]);

            // Load relationships for response
            $team->load([
                'lead:id,name,email',
                'creator:id,name,email',
                'members' => function ($query) {
                    $query->select('users.id', 'users.name', 'users.email')
                        ->withPivot(['role', 'joined_at']);
                }
            ]);

            return $team;
        });
    }

    /**
     * Get team details with members and tasks overview
     *
     * @param Team $team
     * @return Team
     */
    public function getTeamDetails(Team $team): Team
    {
        return $team->load([
            'lead:id,name,email',
            'creator:id,name,email',
            'members' => function ($query) {
                $query->select('users.id', 'users.name', 'users.email', 'users.created_at')
                    ->withPivot(['role', 'joined_at', 'uuid'])
                    ->orderBy('team_user.joined_at', 'desc');
            },
            'tasks' => function ($query) {
                $query->select('id', 'team_id', 'title', 'status', 'priority', 'due_date', 'created_at')
                    ->with('assignee:id,name,email')
                    ->latest()
                    ->limit(10);
            }
        ])
        ->loadCount([
            'members',
            'tasks',
            'tasks as completed_tasks_count' => function ($query) {
                $query->where('status', 'completed');
            },
            'tasks as pending_tasks_count' => function ($query) {
                $query->where('status', 'pending');
            },
            'tasks as in_progress_tasks_count' => function ($query) {
                $query->where('status', 'in_progress');
            }
        ]);
    }

    /**
     * Update team details
     *
     * @param Team $team
     * @param array $data
     * @return Team
     */
    public function updateTeam(Team $team, array $data): Team
    {
        // Some static analyzers can misread Eloquent's `update(array $attributes)` signature.
        // `fill()->save()` is equivalent and avoids the false-positive "0 args accepted" error.
        $team->fill($data);
        $team->save();
        
        // Reload relationships
        $team->load([
            'lead:id,name,email',
            'creator:id,name,email',
            'members' => function ($query) {
                $query->select('users.id', 'users.name', 'users.email')
                    ->withPivot(['role', 'joined_at']);
            }
        ]);

        return $team;
    }

    /**
     * Delete a team
     *
     * @param Team $team
     * @return bool
     */
    public function deleteTeam(Team $team): bool
    {
        return DB::transaction(function () use ($team) {
            // Detach all members
            $team->members()->detach();
            
            /**
             * Some static analyzers confuse `$team->delete()` with the query-builder signature.
             * Use an explicit model query delete to keep both runtime + static analysis happy.
             */
            return (bool) Team::query()->whereKey($team->getKey())->delete();
        });
    }

    /**
     * Check if user is team member
     *
     * @param Team $team
     * @param User $user
     * @return bool
     */
    public function isTeamMember(Team $team, User $user): bool
    {
        return $team->members()->where('user_id', $user->id)->exists();
    }

    /**
     * Check if user is team lead
     *
     * @param Team $team
     * @param User $user
     * @return bool
     */
    public function isTeamLead(Team $team, User $user): bool
    {
        return $team->lead_id === $user->id;
    }
}