<?php

namespace App\Services\Common\FileStorage;

use Illuminate\Http\UploadedFile;

interface FileStorageService
{
    public function upload(UploadedFile $file, string $directory = 'uploads', ?string $filename = null): array;
    public function delete(string $path): bool;
    public function exists(string $path): bool;
    public function url(string $path): string;
    public function size(string $path): int;
    public function get(string $path): string;
}
