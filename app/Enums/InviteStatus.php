<?php

namespace App\Enums;

/**
 * Enum InviteStatus.
 *
 * Defines the available statuses for team invites.
 */
enum InviteStatus: string
{
	case PENDING = 'pending';
	case ACCEPTED = 'accepted';
	case EXPIRED = 'expired';
	case REVOKED = 'revoked';
}
