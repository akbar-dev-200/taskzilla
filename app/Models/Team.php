<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Team extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'uuid',
        'name',
        'lead_id',
        'created_by',
    ];

    public function lead(): BelongsTo
    {
        return $this->belongsTo(User::class, 'lead_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'team_user')
            ->withPivot(['joined_at', 'uuid'])
            ->withTimestamps();
    }

    public function teamUsers(): HasMany
    {
        return $this->hasMany(TeamUser::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function invites(): HasMany
    {
        return $this->hasMany(Invite::class);
    }
}

