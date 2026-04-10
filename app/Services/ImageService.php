<?php

namespace App\Services;

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class ImageService
{
    /**
     * Avatar storage directory
     */
    private const AVATAR_DIR = 'avatars';
    private const AVATAR_DISK = 'public';
    
    /**
     * Resume storage directory
     */
    private const RESUME_DIR = 'resumes';
    private const RESUME_DISK = 'public';

    public function __construct()
    {
        $this->ensureDirectoriesExist();
    }

    /**
     * Ensure required directories exist
     */
    private function ensureDirectoriesExist(): void
    {
        $publicPath = storage_path('app/public');
        
        // Create avatars directory
        $avatarsPath = $publicPath . '/' . self::AVATAR_DIR;
        if (!File::exists($avatarsPath)) {
            File::makeDirectory($avatarsPath, 0755, true);
        }
        
        // Create resumes directory
        $resumesPath = $publicPath . '/' . self::RESUME_DIR;
        if (!File::exists($resumesPath)) {
            File::makeDirectory($resumesPath, 0755, true);
        }
    }

    /**
     * Process and store profile photo with compression
     * 
     * @param UploadedFile $file
     * @return string Path to stored file relative to public disk
     */
    public function storeProfilePhoto(UploadedFile $file): string
    {
        try {
            // Create image manager with GD driver
            $manager = new ImageManager(new GdDriver());
            
            // Load, resize and compress image
            // Cover to square 500x500, optimize quality
            $image = $manager->read($file->getPathname())
                ->cover(500, 500)
                ->toJpeg(quality: 85);
            
            // Generate unique filename with timestamp
            $filename = 'profile_' . time() . '_' . uniqid() . '.jpg';
            $filePath = self::AVATAR_DIR . '/' . $filename;
            
            // Store encoded image to public disk
            Storage::disk(self::AVATAR_DISK)->put(
                $filePath,
                (string) $image
            );
            
            \Illuminate\Support\Facades\Log::debug('Profile photo stored at: ' . $filePath);
            
            return $filePath;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Profile photo upload error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Store resume/CV file
     * 
     * @param UploadedFile $file
     * @return string Path to stored file relative to public disk
     */
    public function storeResume(UploadedFile $file): string
    {
        try {
            $filename = 'resume_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            
            $path = $file->storeAs(self::RESUME_DIR, $filename, self::RESUME_DISK);
            
            \Illuminate\Support\Facades\Log::debug('Resume stored at: ' . $path);
            
            return $path;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Resume upload error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Delete file from public storage
     * 
     * @param string $path - Path relative to public disk
     * @return bool
     */
    public function deleteFile(string $path): bool
    {
        try {
            if ($path && Storage::disk(self::AVATAR_DISK)->exists($path)) {
                $deleted = Storage::disk(self::AVATAR_DISK)->delete($path);
                
                if ($deleted) {
                    \Illuminate\Support\Facades\Log::debug('File deleted: ' . $path);
                }
                
                return $deleted;
            }
            
            return false;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('File deletion error: ' . $e->getMessage());
            return false;
        }
    }
}
