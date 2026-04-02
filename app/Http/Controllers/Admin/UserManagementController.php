<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class UserManagementController extends Controller
{
    public function index(Request $request)
    {
        // Check if user is admin
        /** @var User $user */
        $user = Auth::user();
        if (!$user->hasRole('admin')) {
            abort(403, 'Unauthorized to manage users');
        }

        // Get all users with pagination
        $page = $request->get('page', 1);
        $perPage = 15;
        $searchQuery = $request->get('search', '');
        $filterRole = $request->get('role', '');

        $query = User::query();

        // Search by name or email
        if ($searchQuery) {
            $query->where('name', 'like', "%{$searchQuery}%")
                  ->orWhere('email', 'like', "%{$searchQuery}%");
        }

        // Filter by role
        if ($filterRole && $filterRole !== 'all') {
            $query->where('role', $filterRole);
        }

        $users = $query->latest('created_at')
                       ->paginate($perPage, ['*'], 'page', $page);

        // Transform users for frontend
        $userData = $users->map(function (User $user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone ?? 'N/A',
                'role' => $user->role ?? 'user',
                'created_at' => $user->created_at->format('Y-m-d'),
                'email_verified_at' => $user->email_verified_at ? $user->email_verified_at->format('Y-m-d H:i') : 'Not verified',
            ];
        });

        return Inertia::render('Admin/Users', [
            'users' => $userData,
            'pagination' => [
                'current_page' => $users->currentPage(),
                'total' => $users->total(),
                'per_page' => $users->perPage(),
                'last_page' => $users->lastPage(),
            ],
            'filters' => [
                'search' => $searchQuery,
                'role' => $filterRole,
            ]
        ]);
    }

    public function show(User $user)
    {
        // Check authorization
        /** @var User $authUser */
        $authUser = Auth::user();
        if (!$authUser->hasRole('admin')) {
            abort(403, 'Unauthorized');
        }

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'role' => $user->role,
                'address' => $user->address,
                'city' => $user->city,
                'province' => $user->province,
                'postal_code' => $user->postal_code,
                'date_of_birth' => $user->date_of_birth,
                'gender' => $user->gender,
                'bio' => $user->bio,
                'created_at' => $user->created_at->format('Y-m-d H:i:s'),
                'email_verified_at' => $user->email_verified_at?->format('Y-m-d H:i:s'),
            ]
        ]);
    }

    public function update(Request $request, User $user)
    {
        // Check authorization
        /** @var User $authUser */
        $authUser = Auth::user();
        if (!$authUser->hasRole('admin')) {
            abort(403, 'Unauthorized');
        }

        // Validate input
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'role' => 'required|in:user,recruiter,admin',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'province' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:10',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'bio' => 'nullable|string|max:1000',
        ]);

        // Update user
        $user->update($validated);

        // Log the activity
        activity()
            ->causedBy($authUser)
            ->performedOn($user)
            ->withProperties([
                'attributes' => $validated,
                'old' => $user->getOriginal(),
            ])
            ->log('updated');

        return back()->with('message', 'User berhasil diperbarui!');
    }

    public function destroy(User $user)
    {
        // Check authorization
        /** @var User $authUser */
        $authUser = Auth::user();
        if (!$authUser->hasRole('admin')) {
            abort(403, 'Unauthorized');
        }

        // Prevent deleting self
        if ($authUser->id === $user->id) {
            return back()->with('error', 'Tidak bisa menghapus user yang sedang login!');
        }

        // Log the activity before deletion
        activity()
            ->causedBy($authUser)
            ->performedOn($user)
            ->withProperties([
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ])
            ->log('deleted');

        // Delete user
        $user->delete();

        return back()->with('message', 'User berhasil dihapus!');
    }

    public function updatePassword(Request $request, User $user)
    {
        // Check authorization
        /** @var User $authUser */
        $authUser = Auth::user();
        if (!$authUser->hasRole('admin')) {
            abort(403, 'Unauthorized');
        }

        // Validate password
        $validated = $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Update password
        $user->update([
            'password' => Hash::make($validated['password'])
        ]);

        // Log the activity
        activity()
            ->causedBy($authUser)
            ->performedOn($user)
            ->log('password changed');

        return back()->with('message', 'Password user berhasil diubah!');
    }
}
