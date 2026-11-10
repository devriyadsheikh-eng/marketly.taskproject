<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PublicImageStorage
{
    public function store(UploadedFile $image, string $directory): string
    {
        $path = $image->store($directory, 'public');

        if (! is_string($path) || $path === '') {
            throw new \RuntimeException('The uploaded image could not be stored.');
        }

        return $path;
    }

    public function delete(?string $path, string $directory): void
    {
        if ($path && Str::startsWith($path, $directory.'/') && ! str_contains($path, '..')) {
            Storage::disk('public')->delete($path);
        }
    }
}
