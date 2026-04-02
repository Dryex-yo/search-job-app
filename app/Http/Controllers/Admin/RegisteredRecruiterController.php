<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredRecruiterController extends Controller
{
    /**
     * Display the recruiter registration form.
     * Only accessible by admin users.
     */
    public function create(): Response
    {
        return Inertia::render('Admin/RegisterRecruiter');
    }

    /**
     * Handle an incoming recruiter registration request.
     * Only admin users can register recruiters.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'phone' => 'nullable|string|max:20',
        ]);

        // Create the recruiter user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'recruiter',
            'phone' => $request->phone,
            'email_verified_at' => now(), // Auto-verify since admin is creating it
        ]);

        // Assign recruiter role for Spatie Permission system
        $user->assignRole('recruiter');

        return redirect(route('admin.dashboard', absolute: false))->with('message', 'Recruiter berhasil ditambahkan!');
    }
}
