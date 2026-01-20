<?php

namespace App\Services\Common\Avatar;

use App\Models\User;
use App\Services\Common\FileStorage\FileStorageManager;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

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
    public function generateAndUploadAvatar(User $user, UploadedFile $file): array
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
     * Generate a default avatar (SVG with initials) and store it on S3.
     * Example: "John Doe" -> "JD".
     *
     * @return array{path:string, url:string, initials:string}
     */
    public function generateDefaultAvatar(User $user): array
    {
        $previousPath = $user->avatar;

        $initials = $this->initialsFromName($user->name);
        $color = $this->colorFromSeed($user->uuid ?: (string) $user->id);

        $svg = $this->buildInitialsSvg($initials, $color);

        $directory = "avatars/{$user->id}";
        $filename = 'default-' . Str::uuid()->toString() . '.svg';
        $path = trim($directory, '/') . '/' . $filename;

        /**
         * Write directly to S3 (no local file required).
         *
         * IMPORTANT:
         * Many S3 buckets disable ACLs (Object Ownership: "Bucket owner enforced").
         * In that setup, attempting to set "public" visibility can make uploads fail.
         *
         * So we do NOT force visibility here; access should be controlled via bucket policy
         * or via signed URLs (temporaryUrl) if you keep the bucket private.
         */
        $ok = Storage::disk('s3')->put($path, $svg, [
            'ContentType' => 'image/svg+xml',
        ]);

        if (!$ok) {
            Log::warning('Failed to upload default avatar to S3 (put returned false)', [
                'user_id' => $user->id,
                'path' => $path,
                'bucket' => config('filesystems.disks.s3.bucket'),
                'region' => config('filesystems.disks.s3.region'),
            ]);
        }

        $user->avatar = $path;
        $user->save();

        // Best-effort cleanup of the previous avatar to avoid orphaned files.
        if (!empty($previousPath) && $previousPath !== $path) {
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
            'path' => $path,
            'url' => $this->storage->url($path),
            'initials' => $initials,
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

    protected function initialsFromName(string $name): string
    {
        $name = trim(preg_replace('/\s+/', ' ', $name) ?? '');
        if ($name === '') {
            return 'U';
        }

        $parts = array_values(array_filter(explode(' ', $name)));

        $first = mb_substr($parts[0] ?? 'U', 0, 1);
        $second = mb_substr($parts[1] ?? '', 0, 1);

        $initials = mb_strtoupper($first . $second);

        // Ensure exactly 2 chars when possible, otherwise 1.
        return $initials !== '' ? $initials : 'U';
    }

    /**
     * Pick a deterministic background color so the same user always gets the same vibe.
     */
    protected function colorFromSeed(string $seed): string
    {
        $palette = [
            '#2563EB', // blue
            '#7C3AED', // purple
            '#DB2777', // pink
            '#059669', // emerald
            '#D97706', // amber
            '#0EA5E9', // sky
        ];

        $idx = crc32($seed) % count($palette);
        return $palette[(int) $idx];
    }

    protected function buildInitialsSvg(string $initials, string $bgColor): string
    {
        $safeInitials = htmlspecialchars($initials, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="256" height="256" viewBox="0 0 256 256" role="img" aria-label="Avatar {$safeInitials}">
  <rect width="256" height="256" fill="{$bgColor}" rx="48" />
  <text x="50%" y="52%" text-anchor="middle" dominant-baseline="middle"
        font-family="Inter, system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif"
        font-size="96" font-weight="700" fill="#FFFFFF">{$safeInitials}</text>
</svg>
SVG;
    }
}
