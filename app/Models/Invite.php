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

    /**
     * Check if the invitation is expired.
     *
     * @return bool
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Check if the invitation is pending.
     *
     * @return bool
     */
    public function isPending(): bool
    {
        return $this->status === InviteStatus::PENDING && !$this->isExpired();
    }

    /**
     * Check if the invitation is accepted.
     *
     * @return bool
     */
    public function isAccepted(): bool
    {
        return $this->status === InviteStatus::ACCEPTED;
    }

    /**
     * Check if the invitation is revoked.
     *
     * @return bool
     */
    public function isRevoked(): bool
    {
        return $this->status === InviteStatus::REVOKED;
    }

    /**
     * Get the acceptance URL.
     *
     * @return string
     */
    public function getAcceptanceUrl(): string
    {
        return config('app.frontend_url') . '/invites/accept?token=' . $this->token;
    }

    /**
     * Scope to get only pending invitations.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePending($query)
    {
        return $query->where('status', InviteStatus::PENDING)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }

    /**
     * Scope to get expired invitations.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<=', now())
            ->where('status', InviteStatus::PENDING);
    }

    /**
     * Scope to filter by team.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $teamId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForTeam($query, int $teamId)
    {
        return $query->where('team_id', $teamId);
    }

    /**
     * Scope to filter by email.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $email
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForEmail($query, string $email)
    {
        return $query->where('email', $email);
    }
}

