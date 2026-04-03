<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Services\TenantService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TenantController extends Controller
{
    protected $tenantService;

    public function __construct(TenantService $tenantService)
    {
        $this->tenantService = $tenantService;
    }

    /**
     * Display all tenants (admin only)
     */
    public function index()
    {
        $this->authorize('viewAny', Tenant::class);

        $tenants = Tenant::paginate(15);

        return Inertia::render('Admin/Tenants/Index', [
            'tenants' => $tenants,
        ]);
    }

    /**
     * Show create tenant form
     */
    public function create()
    {
        $this->authorize('create', Tenant::class);

        return Inertia::render('Admin/Tenants/Create');
    }

    /**
     * Store a new tenant
     */
    public function store(Request $request)
    {
        $this->authorize('create', Tenant::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'domain' => 'required|string|max:255|unique:tenants',
            'owner_name' => 'required|string|max:255',
            'owner_email' => 'required|email|max:255',
            'owner_phone' => 'nullable|string|max:20',
            'industry' => 'nullable|string|max:255',
            'company_size' => 'nullable|string|in:startup,small,medium,large,enterprise',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'subscription_plan' => 'nullable|string|in:free,starter,professional,enterprise',
            'trial_ends_at' => 'nullable|date',
        ]);

        try {
            $tenant = $this->tenantService->createTenant($validated);

            return redirect()->route('tenants.show', $tenant->id)
                ->with('success', "Tenant '{$tenant->name}' created successfully.");
        } catch (\Exception $e) {
            return back()->withErrors($e->getMessage());
        }
    }

    /**
     * Show tenant details
     */
    public function show(Tenant $tenant)
    {
        $this->authorize('view', $tenant);

        return Inertia::render('Admin/Tenants/Show', [
            'tenant' => $tenant,
            'stats' => [
                'total_users' => $tenant->recruiters()->count(),
                'total_jobs' => $tenant->jobs()->count(),
                'total_applications' => $tenant->applications()->count(),
            ],
        ]);
    }

    /**
     * Show edit form
     */
    public function edit(Tenant $tenant)
    {
        $this->authorize('update', $tenant);

        return Inertia::render('Admin/Tenants/Edit', [
            'tenant' => $tenant,
        ]);
    }

    /**
     * Update tenant
     */
    public function update(Request $request, Tenant $tenant)
    {
        $this->authorize('update', $tenant);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'domain' => "required|string|max:255|unique:tenants,domain,{$tenant->id}",
            'owner_name' => 'required|string|max:255',
            'owner_email' => 'required|email|max:255',
            'owner_phone' => 'nullable|string|max:20',
            'industry' => 'nullable|string|max:255',
            'company_size' => 'nullable|string|in:startup,small,medium,large,enterprise',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'subscription_plan' => 'nullable|string|in:free,starter,professional,enterprise',
            'status' => 'required|string|in:active,suspended,inactive',
            'trial_ends_at' => 'nullable|date',
        ]);

        $tenant->update($validated);

        return redirect()->route('tenants.show', $tenant->id)
            ->with('success', 'Tenant updated successfully.');
    }

    /**
     * Suspend tenant
     */
    public function suspend(Tenant $tenant)
    {
        $this->authorize('update', $tenant);

        $this->tenantService->updateTenantStatus($tenant, 'suspended');

        return back()->with('success', 'Tenant suspended successfully.');
    }

    /**
     * Activate tenant
     */
    public function activate(Tenant $tenant)
    {
        $this->authorize('update', $tenant);

        $this->tenantService->updateTenantStatus($tenant, 'active');

        return back()->with('success', 'Tenant activated successfully.');
    }

    /**
     * Delete tenant
     */
    public function destroy(Tenant $tenant)
    {
        $this->authorize('delete', $tenant);

        try {
            $this->tenantService->deleteTenant($tenant);

            return redirect()->route('tenants.index')
                ->with('success', 'Tenant deleted successfully.');
        } catch (\Exception $e) {
            return back()->withErrors($e->getMessage());
        }
    }
}
