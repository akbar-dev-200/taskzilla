<?php

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('status', enum_values(TaskStatus::class))->default(TaskStatus::PENDING->value);
            $table->enum('priority', enum_values(TaskPriority::class))->default(TaskPriority::MEDIUM->value);
            $table->date('due_date')->nullable();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->foreignId('assigned_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            
            $table->index('uuid');
            $table->index('status');
            $table->index('priority');
            $table->index('due_date');
            $table->index('team_id');
            $table->index('assigned_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
