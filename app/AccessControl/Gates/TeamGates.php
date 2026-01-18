<?php

namespace App\AccessControl\Gates;

use App\Enums\UserRole;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

class TeamGates
{
    public static function register(): void
    {
        // Any authenticated user can create a team
        Gate::define('create-team', function (User $user) {
            return true;
        });

        // Only team lead or admin can update team
        Gate::define('update-team', function (User $user, Team $team) {
            return $user->id === $team->lead_id || $user->role === UserRole::ADMIN;
        });

        // Only admin can delete team
        Gate::define('delete-team', function (User $user, Team $team) {
            return $user->role === UserRole::ADMIN;
        });

        // User can view team if they are a member
        Gate::define('view-team', function (User $user, Team $team) {
            return $team->members()->where('user_id', $user->id)->exists();
        });

        // User can view team list if authenticated
        Gate::define('view-team-list', function (User $user) {
            return true;
        });
    }
}
