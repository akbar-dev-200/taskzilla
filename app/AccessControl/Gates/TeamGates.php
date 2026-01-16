<?php

namespace App\AccessControl\Gates;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

class TeamGates
{
    public static function register(): void
    {
        Gate::define('create-team', function (User $user) {
            return $user->role === UserRole::ADMIN;
        });

        Gate::define('update-team', function (User $user) {
            return $user->role === UserRole::ADMIN;
        });

        Gate::define('delete-team', function (User $user) {
            return $user->role === UserRole::ADMIN;
        });
    }
}
