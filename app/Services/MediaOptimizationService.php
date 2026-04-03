<?php

namespace App\Services;

use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Image\Enums\AlignPosition;

/**
 * Media optimization service for handling image processing
 * Configures automatic image resizing and WebP conversion
 */
class MediaOptimizationService
{
    /**
     * Get conversions for user avatar images
     */
    public static function getAvatarConversions(Media $media): void
    {
        $media
            ->addMediaConversion('avatar-small')
            ->width(64)
            ->height(64)
            ->sharpen(10)
            ->format('webp')
            ->performOnCollections('avatars')
            ->nonQueued();

        $media
            ->addMediaConversion('avatar-medium')
            ->width(128)
            ->height(128)
            ->sharpen(10)
            ->format('webp')
            ->performOnCollections('avatars')
            ->nonQueued();

        $media
            ->addMediaConversion('avatar-large')
            ->width(256)
            ->height(256)
            ->sharpen(10)
            ->format('webp')
            ->performOnCollections('avatars')
            ->nonQueued();

        $media
            ->addMediaConversion('avatar-thumbnail')
            ->width(32)
            ->height(32)
            ->format('webp')
            ->performOnCollections('avatars')
            ->nonQueued();
    }

    /**
     * Get conversions for resume/document thumbnails
     */
    public static function getDocumentConversions(Media $media): void
    {
        $media
            ->addMediaConversion('document-thumb')
            ->width(200)
            ->height(280)
            ->format('webp')
            ->performOnCollections('resumes', 'documents')
            ->nonQueued();
    }

    /**
     * Get conversions for job images/logos
     */
    public static function getJobImageConversions(Media $media): void
    {
        $media
            ->addMediaConversion('logo-small')
            ->width(80)
            ->height(80)
            ->format('webp')
            ->performOnCollections('job-images')
            ->nonQueued();

        $media
            ->addMediaConversion('logo-medium')
            ->width(200)
            ->height(200)
            ->format('webp')
            ->performOnCollections('job-images')
            ->nonQueued();

        $media
            ->addMediaConversion('logo-large')
            ->width(400)
            ->height(400)
            ->format('webp')
            ->performOnCollections('job-images')
            ->nonQueued();
    }
}
