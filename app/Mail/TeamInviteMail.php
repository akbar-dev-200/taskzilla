<?php

namespace App\Mail;

use App\Models\Invite;
use App\Models\Team;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TeamInviteMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public Invite $invite,
        public Team $team,
        public User $inviter
    ) {
        //
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "You've been invited to join {$this->team->name}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $acceptUrl = config('app.frontend_url') . '/invites/accept?token=' . $this->invite->token;

        return new Content(
            view: 'emails.team-invite',
            with: [
                'teamName' => $this->team->name,
                'inviterName' => $this->inviter->name,
                'role' => $this->invite->role->value,
                'acceptUrl' => $acceptUrl,
                'expiresAt' => $this->invite->expires_at?->format('M d, Y'),
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
