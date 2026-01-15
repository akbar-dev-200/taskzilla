<?php

namespace App\Enums;

/**
 * Enum UserRole.
 *
 * Defines the available user roles in the system.
 */
enum UserRole: string
{
	case ADMIN = 'admin';
	case LEAD = 'lead';
	case MEMBER = 'member';
}
