<?php

namespace App\Enums;

/**
 * Enum TaskStatus.
 *
 * Defines the available statuses for tasks.
 */
enum TaskStatus: string
{
	case PENDING = 'pending';
	case IN_PROGRESS = 'in_progress';
	case COMPLETED = 'completed';
}
