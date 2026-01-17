<?php

namespace App\Services\Module\Invite;

use App\Enums\InviteStatus;
use App\Enums\UserRole;
use App\Mail\TeamInviteMail;
use App\Models\Invite;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class InviteService
{
    /**
     * Send invitations to multiple users for a team.
     *
     * @param Team $team
     * @param array $invitations Array of ['email' => 'user@example.com', 'role' => 'member']
     * @param User $inviter
     * @return array
     */
    public function sendInvitations(Team $team, array $invitations, User $inviter): array
    {
        $results = [];

        DB::transaction(function () use ($team, $invitations, $inviter, &$results) {
            foreach ($invitations as $invitation) {
                $email = $invitation['email'];
                $role = UserRole::from($invitation['role']);

                // Check if user is already a team member
                if ($this->isUserAlreadyMember($team, $email)) {
                    $results[] = [
                        'email' => $email,
                        'success' => false,
                        'message' => 'User is already a member of this team',
                    ];
                    continue;
                }

                // Check if there's already a pending invitation
                $existingInvite = Invite::where('email', $email)
                    ->where('team_id', $team->id)
                    ->where('status', InviteStatus::PENDING)
                    ->first();

                if ($existingInvite) {
                    $results[] = [
                        'email' => $email,
                        'success' => false,
                        'message' => 'Pending invitation already exists',
                    ];
                    continue;
                }

                // Create the invitation
                $invite = Invite::create([
                    'email' => $email,
                    'invited_by' => $inviter->id,
                    'team_id' => $team->id,
                    'role' => $role,
                    'status' => InviteStatus::PENDING,
                    'token' => Str::random(64),
                    'expires_at' => now()->addDays(7), // 7 days expiration
                ]);

                // Send email notification
                try {
                    Mail::to($email)->send(new TeamInviteMail($invite, $team, $inviter));
                    $results[] = [
                        'email' => $email,
                        'success' => true,
                        'message' => 'Invitation sent successfully',
                        'invite_id' => $invite->uuid,
                    ];
                } catch (\Exception $e) {
                    $results[] = [
                        'email' => $email,
                        'success' => false,
                        'message' => 'Failed to send email: ' . $e->getMessage(),
                    ];
                }
            }
        });

        return $results;
    }

    /**
     * Accept an invitation.
     *
     * @param string $token
     * @param User|null $user
     * @return array
     */
    public function acceptInvitation(string $token, ?User $user = null): array
    {
        $invite = Invite::where('token', $token)
            ->where('status', InviteStatus::PENDING)
            ->first();

        if (!$invite) {
            return [
                'success' => false,
                'message' => 'Invalid or expired invitation',
            ];
        }

        // Check if invitation has expired
        if ($invite->expires_at && $invite->expires_at->isPast()) {
            $invite->update(['status' => InviteStatus::EXPIRED]);
            return [
                'success' => false,
                'message' => 'This invitation has expired',
            ];
        }

        // If user is not provided, check if user exists with this email
        if (!$user) {
            $user = User::where('email', $invite->email)->first();
            
            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'Please register first before accepting the invitation',
                    'requires_registration' => true,
                ];
            }
        }

        // Verify email matches
        if ($user->email !== $invite->email) {
            return [
                'success' => false,
                'message' => 'Email mismatch. Please use the account associated with this invitation',
            ];
        }

        DB::transaction(function () use ($invite, $user) {
            // Add user to team with specified role
            $invite->team->members()->syncWithoutDetaching([
                $user->id => [
                    'role' => $invite->role,
                    'joined_at' => now(),
                ]
            ]);

            // Update invitation status
            $invite->update([
                'status' => InviteStatus::ACCEPTED,
                'accepted_at' => now(),
            ]);
        });

        return [
            'success' => true,
            'message' => 'Invitation accepted successfully',
            'team' => $invite->team,
            'role' => $invite->role->value,
        ];
    }

    /**
     * Revoke an invitation.
     *
     * @param int $inviteId
     * @param User $user
     * @return array
     */
    public function revokeInvitation(int $inviteId, User $user): array
    {
        $invite = Invite::where('id', $inviteId)
            ->where('invited_by', $user->id)
            ->first();

        if (!$invite) {
            return [
                'success' => false,
                'message' => 'Invitation not found or you do not have permission to revoke it',
            ];
        }

        if ($invite->status !== InviteStatus::PENDING) {
            return [
                'success' => false,
                'message' => 'Only pending invitations can be revoked',
            ];
        }

        $invite->update(['status' => InviteStatus::REVOKED]);

        return [
            'success' => true,
            'message' => 'Invitation revoked successfully',
        ];
    }

    /**
     * Get all invitations for a team.
     *
     * @param Team $team
     * @param string|null $status
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getTeamInvitations(Team $team, ?string $status = null)
    {
        $query = Invite::where('team_id', $team->id)
            ->with(['inviter', 'team']);

        if ($status) {
            $query->where('status', $status);
        }

        return $query->latest()->get();
    }

    /**
     * Get pending invitations for a user by email.
     *
     * @param string $email
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUserPendingInvitations(string $email)
    {
        return Invite::where('email', $email)
            ->where('status', InviteStatus::PENDING)
            ->where('expires_at', '>', now())
            ->with(['team', 'inviter'])
            ->latest()
            ->get();
    }

    /**
     * Check if user is already a member of the team.
     *
     * @param Team $team
     * @param string $email
     * @return bool
     */
    private function isUserAlreadyMember(Team $team, string $email): bool
    {
        return $team->members()->whereHas('users', function ($query) use ($email) {
            $query->where('email', $email);
        })->exists() || User::where('email', $email)->whereHas('teams', function ($query) use ($team) {
            $query->where('teams.id', $team->id);
        })->exists();
    }
}
