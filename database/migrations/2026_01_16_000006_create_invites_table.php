<?php

use App\Enums\InviteStatus;
use App\Enums\UserRole;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('invites', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('email');
            $table->foreignId('invited_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->enum('role', enum_values(UserRole::class))->default(UserRole::MEMBER->value);
            $table->string('token')->unique();
            $table->enum('status', enum_values(InviteStatus::class))->default(InviteStatus::PENDING->value);
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamps();
            
            $table->index('uuid');
            $table->index('email');
            $table->index('token');
            $table->index('status');
            $table->index('invited_by');
            $table->index('team_id');
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invites');
    }
};
