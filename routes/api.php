<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Invite\InviteController;
use App\Http\Controllers\Team\TeamController;
use Illuminate\Support\Facades\Route;


// Public routes - no authentication required
Route::post('/register', [RegisteredUserController::class, 'store'])->name('api.register');
Route::post('/login', [AuthenticatedSessionController::class, 'login'])->name('api.login');

// Protected routes - require authentication
Route::middleware('auth:sanctum')->group(function () {

    // Authentication routes
    Route::post('/logout', [AuthenticatedSessionController::class, 'logout'])->name('api.logout');

    // Team management routes
    Route::prefix('teams')->group(function () {
        // List all teams user belongs to (authenticated users)
        Route::get('/', [TeamController::class, 'teamList'])->name('team.index');
        
        // Create a new team (any authenticated user can create)
        Route::post('/', [TeamController::class, 'teamCreate'])->name('team.store');
        
        // Show team details with members and tasks overview (team members only)
        Route::get('/{team}', [TeamController::class, 'teamShow'])->name('team.show');
        
        // Update team name (team lead or admin only)
        Route::put('/{team}', [TeamController::class, 'teamUpdate'])->name('team.update');
        Route::patch('/{team}', [TeamController::class, 'teamUpdate'])->name('team.update.patch');
        
        // Delete team (admin only)
        Route::delete('/{team}', [TeamController::class, 'teamDelete'])->name('team.destroy');
    });

    // Invitation routes
    Route::prefix('invites')->group(function () {
        // Send invitations (team admin/creator only - checked in controller)
        Route::post('/', [InviteController::class, 'sendInvitations'])->name('invite.send');
        
        // Accept invitation (authenticated users)
        Route::post('/accept', [InviteController::class, 'acceptInvitation'])->name('invite.accept');
        
        // Revoke invitation (only who sent it)
        Route::delete('/{inviteId}', [InviteController::class, 'revokeInvitation'])->name('invite.revoke');
        
        // Get team invitations (team admin/creator only)
        Route::get('/team/{teamId}', [InviteController::class, 'getTeamInvitations'])->name('invite.team');
        
        // Get my pending invitations
        Route::get('/my-pending', [InviteController::class, 'getMyPendingInvitations'])->name('invite.my-pending');
    });
});