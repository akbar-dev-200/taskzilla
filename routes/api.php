<?php

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
});