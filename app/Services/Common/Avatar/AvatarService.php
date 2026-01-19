<?php

namespace App\Services\Common\Avatar;

use App\Models\User;
use App\Services\Common\FileStorage\FileStorageManager;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AvatarService
{
    public function __construct(
        protected FileStorageManager $storage,
    ) {}

    /**
     * Upload (or replace) a user's avatar on S3 and store the S3 key in `users.avatar`.
     *
     * @return array{path:string, url:string}
     */
    public function upload(User $user, UploadedFile $file): array
    {
        $previousPath = $user->avatar;

        // Upload to S3 via FileStorageService binding (S3FileStorageService)
        $uploaded = $this->storage->uploadAvatar($file, (int) $user->id);


        $user->avatar = $uploaded['path'];
        $user->save();


        // Best-effort cleanup of the previous avatar to avoid orphaned files.
        if (!empty($previousPath) && $previousPath !== $uploaded['path']) {
            try {
                $this->storage->delete($previousPath);
            } catch (\Throwable $e) {
                Log::warning('Failed to delete previous avatar from storage', [
                    'user_id' => $user->id,
                    'previous_path' => $previousPath,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return [
            'path' => $uploaded['path'],
            'url' => $uploaded['url'],
        ];
    }

    /**
     * Delete the user's avatar from S3 and clear `users.avatar`.
     */
    public function delete(User $user): bool
    {
        $path = $user->avatar;
        $user->avatar = null;
        $user->save();

        if (empty($path)) {
            return true;
        }

        try {
            return $this->storage->delete($path);
        } catch (\Throwable $e) {
            Log::warning('Failed to delete avatar from storage', [
                'user_id' => $user->id,
                'path' => $path,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Get the avatar URL for the given user (null if not set).
     */
    public function url(User $user): ?string
    {
        if (empty($user->avatar)) {
            return null;
        }

        return $this->storage->url($user->avatar);
    }
}
