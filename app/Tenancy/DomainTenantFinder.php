<?php

namespace App\Tenancy;

use App\Models\Tenant;
use Spatie\Multitenancy\Models\Tenant as TenantModel;
use Spatie\Multitenancy\TenantFinder\TenantFinder;
use Illuminate\Support\Facades\Log;

class DomainTenantFinder extends TenantFinder
{
    public function findForRequest($request): ?TenantModel
    {
        // Get the host/domain from the request
        $host = $request->getHost();
        
        // Remove port number if present (localhost:8000 → localhost)
        if (strpos($host, ':') !== false) {
            $host = explode(':', $host)[0];
        }

        Log::debug('DomainTenantFinder - Looking for tenant', [
            'host' => $host,
            'full_url' => $request->url(),
        ]);

        // Try to find tenant by domain
        $tenant = Tenant::where('domain', $host)->first();

        if ($tenant) {
            Log::debug('DomainTenantFinder - Tenant found', [
                'tenant_id' => $tenant->id,
                'domain' => $host,
            ]);
            return $tenant;
        }

        // Fallback 1: If localhost and no exact match, return first tenant (development)
        if (in_array($host, ['localhost', '127.0.0.1', '::1'])) {
            $fallbackTenant = Tenant::first();
            if ($fallbackTenant) {
                Log::info('DomainTenantFinder - Using fallback tenant for localhost', [
                    'tenant_id' => $fallbackTenant->id,
                    'original_host' => $host,
                ]);
                return $fallbackTenant;
            }
        }

        Log::warning('DomainTenantFinder - No tenant found', [
            'host' => $host,
        ]);

        return null;
    }
}

