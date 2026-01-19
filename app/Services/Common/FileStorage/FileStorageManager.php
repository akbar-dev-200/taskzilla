<?php

namespace App\Services\Common\FileStorage;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class FileStorageManager
{
    // Dependency Injection: Laravel will automatically inject the S3 Service here.
    public function __construct(
        protected FileStorageService $storage
    ) {}

    /**
     * The core upload method with validation capabilities.
     */
    public function uploadWithValidation(
        UploadedFile $file,
        string $directory = 'uploads',
        array $customRules = [],
        ?string $filename = null
    ): array {
        $rules = array_merge(
            ['file' => ['required', 'file', 'max:10240']], // Default 10MB
            ['file' => $customRules]
        );

        $validator = Validator::make(['file' => $file], $rules);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $this->storage->upload($file, $directory, $filename);
    }

    // --- Semantic Helper Methods ---

    public function uploadImage(UploadedFile $file, string $directory = 'images'): array
    {
        return $this->uploadWithValidation($file, $directory, [
            'mimes:jpeg,jpg,png,gif,webp',
            'max:5120', // 5MB
        ]);
    }

    public function uploadDocument(UploadedFile $file, string $directory = 'documents'): array
    {
        return $this->uploadWithValidation($file, $directory, [
            'mimes:pdf,doc,docx,xls,xlsx,txt,csv',
            'max:10240', // 10MB
        ]);
    }

    public function uploadAvatar(UploadedFile $file, int $userId): array
    {
        return $this->uploadImage($file, "avatars/{$userId}");
    }

    // --- Passthrough Methods (Delegation) ---

    public function delete(string $path): bool
    {
        return $this->storage->delete($path);
    }

    public function get(string $path): string
    {
        return $this->storage->get($path);
    }
    
    public function url(string $path): string
    {
        return $this->storage->url($path);
    }
}