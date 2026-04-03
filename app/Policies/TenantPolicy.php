<?php

namespace App\Policies;

use App\Models\Tenant;
use App\Models\User;

class TenantPolicy
{
    /**
     * Determine if the user can view any tenants
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine if the user can view the tenant
     */
    public function view(User $user, Tenant $tenant): bool
    {
        return $user->isAdmin() || $user->tenant_id === $tenant->id;
    }

    /**
     * Determine if the user can create tenants
     */
    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine if the user can update the tenant
     */
    public function update(User $user, Tenant $tenant): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine if the user can delete the tenant
     */
    public function delete(User $user, Tenant $tenant): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine if the user can restore the tenant
     */
    public function restore(User $user, Tenant $tenant): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine if the user can force delete the tenant
     */
    public function forceDelete(User $user, Tenant $tenant): bool
    {
        return $user->isAdmin();
    }
}
