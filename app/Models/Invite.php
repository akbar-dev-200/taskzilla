<?php

namespace App\Models;

use App\Enums\InviteStatus;
use App\Enums\UserRole;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invite extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'uuid',
        'email',
        'invited_by',
        'team_id',
        'role',
        'status',
        'token',
        'expires_at',
        'accepted_at',
    ];

    protected $casts = [
        'role' => UserRole::class,
        'status' => InviteStatus::class,
        'expires_at' => 'datetime',
        'accepted_at' => 'datetime',
    ];

    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}

