<?php

namespace App\Services;

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\GdDriver;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ImageService
{
    /**
     * Process and store profile photo with compression
     * 
     * @param UploadedFile $file
     * @return string Path to stored file
     */
    public function storeProfilePhoto(UploadedFile $file): string
    {
        try {
            // Create image manager with GD driver
            $manager = new ImageManager(driver: 'gd');
            
            // Load and process image
            $image = $manager->read($file->getPathname())
                ->cover(500, 500)
                ->toJpeg(quality: 85);
            
            // Generate unique filename
            $filename = 'profile_' . time() . '_' . uniqid() . '.jpg';
            
            // Store in public disk
            Storage::disk('public')->put(
                'profile-photos/' . $filename,
                $image
            );
            
            return 'profile-photos/' . $filename;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Profile photo upload error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Store resume/CV file
     * 
     * @param UploadedFile $file
     * @return string Path to stored file
     */
    public function storeResume(UploadedFile $file): string
    {
        $filename = 'resume_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        
        $path = $file->storeAs('resumes', $filename, 'public');
        
        return $path;
    }

    /**
     * Delete file from storage
     * 
     * @param string $path
     * @return bool
     */
    public function deleteFile(string $path): bool
    {
        if ($path && Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->delete($path);
        }
        
        return false;
    }
}
