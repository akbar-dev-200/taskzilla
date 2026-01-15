<?php

namespace App\Enums;

/**
 * Enum TaskPriority.
 *
 * Defines the available priority levels for tasks.
 */
enum TaskPriority: string
{
	case LOW = 'low';
	case MEDIUM = 'medium';
	case HIGH = 'high';
}
