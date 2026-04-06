<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    // Create default tenant
    $tenant = \App\Models\Tenant::create([
        'name' => 'Default Tenant',
        'domain' => 'localhost',
        'database' => 'search_job_app',
        'status' => 'active'
    ]);
    
    echo "✓ Tenant created! (ID: {$tenant->id})\n";
    
    // Also set tenant_id for existing users
    $updated = \App\Models\User::whereNull('tenant_id')->update(['tenant_id' => $tenant->id]);
    echo "✓ Updated $updated users with tenant_id\n";
    
    // Show results
    echo "\nTenants:\n";
    foreach (\App\Models\Tenant::all() as $t) {
        echo "  ID: {$t->id}, Name: {$t->name}, Domain: {$t->domain}\n";
    }
    
    echo "\nUsers with tenant_id:\n";
    foreach (\App\Models\User::all() as $u) {
        echo "  ID: {$u->id}, Name: {$u->name}, Email: {$u->email}, Tenant: {$u->tenant_id}\n";
    }
} catch (Exception $e) {
    echo "ERROR: {$e->getMessage()}\n";
}

