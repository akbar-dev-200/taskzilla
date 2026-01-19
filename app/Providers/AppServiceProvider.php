<?php

namespace App\Providers;

use App\AccessControl\Gates\TaskGates;
use App\AccessControl\Gates\TeamGates;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\ServiceProvider;
use App\Services\Common\FileStorage\FileStorageService;
use App\Services\Common\FileStorage\S3FileStorageService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        ResetPassword::createUrlUsing(function (object $notifiable, string $token) {
            return config('app.frontend_url')."/password-reset/$token?email={$notifiable->getEmailForPasswordReset()}";
        });

        // Register Gates
        TeamGates::register();
        TaskGates::register();

        // Bind the interface to the S3 implementation
        $this->app->bind(FileStorageService::class, S3FileStorageService::class);
    }
}
