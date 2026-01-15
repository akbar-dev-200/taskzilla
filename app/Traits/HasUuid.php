<?php

namespace App\Traits;

use Illuminate\Support\Str;

/**
 * Adds a UUID primary identifier for models while keeping numeric IDs.
 */
trait HasUuid
{
    /**
     * Automatically assign a UUID when creating the model.
     */
    protected static function bootHasUuid(): void
    {
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }
}
