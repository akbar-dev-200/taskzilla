<?php

namespace App\Services\Common\FileStorage;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class S3FileStorageService implements FileStorageService
{
    public function __construct(
        protected string $disk = 's3',
        protected int $urlExpirationMinutes = 60
    ) {}

    public function upload(UploadedFile $file, string $directory = 'uploads', ?string $filename = null): array
    {
        $filename = $filename ?? $this->generateUniqueFilename($file);
        $directory = trim($directory, '/');

        // Store file on S3.
        // NOTE: We do not force 'public' visibility here because many modern S3 buckets
        // disable ACLs (Bucket owner enforced). Access should be controlled via bucket
        // policy or signed URLs.
        $path = $file->storeAs($directory, $filename, [
            'disk' => $this->disk,
        ]);

        return [
            'path'          => $path,
            'url'           => $this->url($path),
            'size'          => $file->getSize(),
            'mime_type'     => $file->getMimeType(),
            'original_name' => $file->getClientOriginalName(),
            'extension'     => $file->getClientOriginalExtension(),
        ];
    }

    public function delete(string $path): bool
    {
        return Storage::disk($this->disk)->delete($path);
    }

    public function exists(string $path): bool
    {
        return Storage::disk($this->disk)->exists($path);
    }

    /**
     * Get the S3 URL.
     * Logic: If the file is private, generate a signed URL. If public, return direct URL.
     */
    public function url(string $path): string
    {
        /** @var \Illuminate\Filesystem\FilesystemAdapter $storage */
        $storage = Storage::disk($this->disk);
        return $storage->url($path);
    }

    public function size(string $path): int
    {
        return Storage::disk($this->disk)->size($path);
    }

    public function get(string $path): string
    {
        return Storage::disk($this->disk)->get($path);
    }

    protected function generateUniqueFilename(UploadedFile $file): string
    {
        return (string) Str::uuid() . '.' . $file->getClientOriginalExtension();
    }
}
