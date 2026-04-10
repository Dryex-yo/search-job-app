<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Services\DashboardCacheService;
use App\Services\ImageService;
use Exception;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    private DashboardCacheService $cacheService;
    private ImageService $imageService;

    public function __construct(DashboardCacheService $cacheService, ImageService $imageService)
    {
        $this->cacheService = $cacheService;
        $this->imageService = $imageService;
    }
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('Profile/Edit', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => session('status'),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request)
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        // Invalidate dashboard cache to reflect profile completion changes
        $this->cacheService->invalidateCache($request->user()->id);

        // Return the edit page dengan updated user data
        return Inertia::render('Profile/Edit', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => 'Profil berhasil diperbarui',
        ]);
    }

    /**
     * Upload user's profile photo
     */
    public function uploadProfilePhoto(Request $request)
    {
        $request->validate([
            'profile_photo' => ['required', 'image', 'mimes:jpeg,png,jpg,gif', 'max:5120', 'dimensions:min_width=100,min_height=100'],
        ], [
            'profile_photo.required' => 'Mohon pilih foto profil',
            'profile_photo.image' => 'File harus berupa gambar',
            'profile_photo.mimes' => 'Format harus JPEG, PNG, JPG, atau GIF',
            'profile_photo.max' => 'Ukuran maksimal 5MB',
            'profile_photo.dimensions' => 'Ukuran gambar minimal 100x100 pixel',
        ]);

        $user = $request->user();

        try {
            // Delete old profile photo if exists
            if ($user->profile_photo_path) {
                $this->imageService->deleteFile($user->profile_photo_path);
            }

            // Store new profile photo with compression
            $path = $this->imageService->storeProfilePhoto($request->file('profile_photo'));

            $user->update([
                'profile_photo_path' => $path,
            ]);

            // Refresh user instance to get updated data
            $user->refresh();

            // Invalidate dashboard cache
            $this->cacheService->invalidateCache($user->id);

            \Illuminate\Support\Facades\Log::info('Profile photo uploaded successfully for user ' . $user->id . ': ' . $path);

            // Return JSON response for AJAX/Inertia handling
            return response()->json([
                'success' => true,
                'message' => 'Foto profil berhasil diupload',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'profile_photo_path' => $user->profile_photo_path,
                    'role' => $user->role,
                ],
                'photo_url' => $user->profile_photo_path ? "/storage/{$user->profile_photo_path}" : null,
            ], 200);
        } catch (Exception $e) {
            $errorMsg = 'Upload photo error: ' . $e->getMessage();
            \Illuminate\Support\Facades\Log::error($errorMsg);
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupload foto: ' . $e->getMessage(),
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Upload user's CV/Resume
     */
    public function uploadResume(Request $request): RedirectResponse
    {
        $request->validate([
            'resume' => ['required', 'file', 'mimes:pdf,doc,docx', 'max:5120'],
        ], [
            'resume.required' => 'Mohon pilih file CV/Resume',
            'resume.file' => 'File harus valid',
            'resume.mimes' => 'Format harus PDF, DOC, atau DOCX',
            'resume.max' => 'Ukuran maksimal 5MB',
        ]);

        $user = $request->user();

        try {
            // Delete old resume if exists
            if ($user->resume_path) {
                $this->imageService->deleteFile($user->resume_path);
            }

            // Store new resume
            $path = $this->imageService->storeResume($request->file('resume'));

            $user->update([
                'resume_path' => $path,
            ]);

            // Invalidate dashboard cache
            $this->cacheService->invalidateCache($user->id);

            return Redirect::route('profile.edit')->with('status', 'CV berhasil diupload');
        } catch (\Exception $e) {
            return Redirect::route('profile.edit')->withErrors(['resume' => 'Gagal mengupload CV. Silakan coba lagi.']);
        }
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
