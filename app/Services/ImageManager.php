<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageManager
{
    /**
     * The disk that stores public menu media.
     */
    public const DISK = 'public';

    /**
     * Store an uploaded image and optionally remove the previous one.
     *
     * Files are scoped to a per-tenant directory and the previous file is
     * only deleted when it lives inside that same directory, which prevents
     * one tenant from removing another tenant's media.
     */
    public function store(UploadedFile $file, string $directory, ?string $previous = null): string
    {
        if ($previous) {
            $this->delete($previous, $directory);
        }

        return $file->store($directory, self::DISK);
    }

    /**
     * Safely delete a stored image within the expected tenant directory.
     */
    public function delete(?string $path, string $directory): void
    {
        if (blank($path)) {
            return;
        }

        $normalized = ltrim($path, '/');
        $prefix = trim($directory, '/').'/';

        if (! Str::startsWith($normalized, $prefix)) {
            return;
        }

        Storage::disk(self::DISK)->delete($normalized);
    }

    /**
     * Public URL for a stored image path.
     */
    public function url(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        return Storage::disk(self::DISK)->url($path);
    }

    /**
     * Tenant-scoped directory for a given media type.
     */
    public function directory(int $restaurantId, string $type): string
    {
        return "restaurants/{$restaurantId}/{$type}";
    }
}
