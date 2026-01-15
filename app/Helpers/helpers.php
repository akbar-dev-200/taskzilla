<?php

if (!function_exists('enum_values')) {
	/**
	 * Get all enum values as an array.
	 *
	 * @param  string  $enumClass
	 * @return array
	 */
	function enum_values(string $enumClass): array
	{
		return array_column($enumClass::cases(), 'value');
	}
}
