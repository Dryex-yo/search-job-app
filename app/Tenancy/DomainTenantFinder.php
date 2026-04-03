<?php

namespace App\Tenancy;

use App\Models\Tenant;
use Spatie\Multitenancy\Models\Tenant as TenantModel;
use Spatie\Multitenancy\TenantFinder\TenantFinder;

class DomainTenantFinder extends TenantFinder
{
    public function findForRequest($request): ?TenantModel
    {
        // Get the host/domain from the request
        $host = $request->getHost();
        
        // Remove port number if present
        if (strpos($host, ':') !== false) {
            $host = explode(':', $host)[0];
        }

        // Try to find tenant by domain
        $tenant = Tenant::where('domain', $host)->first();

        return $tenant;
    }
}
