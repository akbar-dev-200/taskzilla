<?php

use App\Http\Controllers\Invite\InviteController;
use App\Http\Controllers\Team\TeamController;
use Illuminate\Support\Facades\Route;


// Protected routes - require authentication
Route::middleware('auth:sanctum')->group(function () {

    // Admin only routes - Team management
    Route::middleware('role:admin')->prefix('teams')->group(function () {
        Route::get('/', [TeamController::class, 'teamList'])->name('team.list');
        Route::post('/', [TeamController::class, 'teamCreate'])->name('team.create');
        Route::delete('/{id}', [TeamController::class, 'teamDelete'])->name('team.delete');
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