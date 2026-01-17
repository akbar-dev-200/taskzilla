<?php

namespace App\Models;

use App\Enums\UserRole;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeamUser extends Model
{
    use HasFactory, HasUuid;

    protected $table = 'team_user';

    protected $fillable = [
        'uuid',
        'team_id',
        'user_id',
        'role',
        'joined_at',
    ];

    protected $casts = [
        'role' => UserRole::class,
        'joined_at' => 'datetime',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

