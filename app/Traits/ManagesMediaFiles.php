<?php

namespace App\Traits;

use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Trait for handling media operations in models
 * Provides helper methods for uploading and managing media files
 */
trait ManagesMediaFiles
{
    /**
     * Upload and attach a media file to a collection
     *
     * @param string $collectionName
     * @param string|null $diskName
     * @return Media|null
     */
    public function uploadMedia($file, string $collectionName, string $diskName = 'public'): ?Media
    {
        if (!$file) {
            return null;
        }

        try {
            return $this->addMedia($file)
                ->toMediaCollection($collectionName, $diskName);
        } catch (\Exception $e) {
            \Log::error('Media upload error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get media URL with conversion
     *
     * @param string $collectionName
     * @param string $conversionName
     * @return string|null
     */
    public function getMediaUrl(string $collectionName, string $conversionName = ''): ?string
    {
        $media = $this->getFirstMedia($collectionName);

        if (!$media) {
            return null;
        }

        if ($conversionName) {
            return $media->getUrl($conversionName);
        }

        return $media->getUrl();
    }

    /**
     * Get optimized image URL (WebP if available, fallback to original)
     *
     * @param string $collectionName
     * @param string $conversionName
     * @return string|null
     */
    public function getOptimizedImageUrl(string $collectionName, string $conversionName = 'medium'): ?string
    {
        $media = $this->getFirstMedia($collectionName);

        if (!$media) {
            return null;
        }

        // Try to get WebP conversion first
        if ($media->hasGeneratedConversion($conversionName)) {
            return $media->getUrl($conversionName);
        }

        // Fallback to original
        return $media->getUrl();
    }

    /**
     * Clear media from a collection
     *
     * @param string $collectionName
     * @return void
     */
    public function clearMedia(string $collectionName): void
    {
        $this->clearMediaCollection($collectionName);
    }

    /**
     * Delete specific media
     *
     * @param int $mediaId
     * @return bool
     */
    public function deleteMedia(int $mediaId): bool
    {
        return $this->media()
            ->where('id', $mediaId)
            ->delete() > 0;
    }
}
