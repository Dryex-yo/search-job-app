<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\User;
use App\Services\TenantService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class TenantRegistrationController extends Controller
{
    protected $tenantService;

    public function __construct(TenantService $tenantService)
    {
        $this->tenantService = $tenantService;
    }

    /**
     * Show tenant registration form
     */
    public function showRegistrationForm()
    {
        if (Auth::check()) {
            abort(403, 'Already authenticated');
        }
        
        return Inertia::render('Tenant/Register', [
            'registerUrl' => route('tenant.register'),
        ]);
    }

    /**
     * Handle tenant registration
     */
    public function register(Request $request)
    {
        if (Auth::check()) {
            abort(403, 'Already authenticated');
        }
        
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'domain' => 'required|string|max:255|unique:tenants',
            'owner_name' => 'required|string|max:255',
            'owner_email' => 'required|email|max:255|unique:users,email',
            'owner_phone' => 'nullable|string|max:20',
            'industry' => 'nullable|string|max:255',
            'company_size' => 'nullable|string|in:startup,small,medium,large,enterprise',
            'password' => 'required|string|min:8|confirmed',
        ]);

        try {
            // Create tenant
            $tenant = $this->tenantService->createTenant([
                'name' => $validated['company_name'],
                'domain' => $validated['domain'],
                'owner_name' => $validated['owner_name'],
                'owner_email' => $validated['owner_email'],
                'owner_phone' => $validated['owner_phone'],
                'industry' => $validated['industry'],
                'company_size' => $validated['company_size'],
                'subscription_plan' => 'free',
                'trial_ends_at' => now()->addDays(14), // 14-day free trial
            ]);

            // Create admin user for tenant
            \Spatie\Multitenancy\Facades\Tenancy::initialize($tenant);
            
            $user = User::create([
                'name' => $validated['owner_name'],
                'email' => $validated['owner_email'],
                'password' => Hash::make($validated['password']),
                'role' => 'admin',
                'tenant_id' => $tenant->id,
            ]);

            $user->assignRole('admin');

            \Spatie\Multitenancy\Facades\Tenancy::forgetCurrent();

            // Login the user
            Auth::login($user);

            return redirect()->route('dashboard')
                ->with('success', 'Tenant registered successfully. Welcome to ' . $validated['company_name'] . '!');
        } catch (\Exception $e) {
            return back()
                ->withInput($request->except('password', 'password_confirmation'))
                ->withErrors(['error' => 'Registration failed: ' . $e->getMessage()]);
        }
    }
}
