<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Tenant;

class DebugTenancyCommand extends Command
{
    protected $signature = 'debug:tenancy {user_id?}';
    protected $description = 'Debug tenancy context for specific user';

    public function handle()
    {
        $userId = $this->argument('user_id') ?? 1;
        
        $this->info("=== TENANCY DEBUG ===\n");

        // Check Tenant table
        $this->info("1. TENANT TABLE CHECK:");
        $tenants = Tenant::all();
        $this->line("   Total tenants: " . $tenants->count());
        foreach ($tenants as $tenant) {
            $this->line("   - ID: {$tenant->id}, Name: {$tenant->name}, Domain: {$tenant->domain}");
        }
        
        if ($tenants->count() === 0) {
            $this->error("\n   ⚠️ NO TENANTS FOUND! Create tenant first.");
            return;
        }

        // Check User
        $this->info("\n2. USER CHECK:");
        $user = User::find($userId);
        if (!$user) {
            $this->error("   ⚠️ User $userId not found!");
            return;
        }
        $this->line("   User: {$user->name} (ID: {$user->id})");
        $this->line("   Email: {$user->email}");
        $this->line("   Tenant ID: " . ($user->tenant_id ?? "NULL ⚠️"));

        // Check database
        $this->info("\n3. DATABASE CHECK:");
        $dbUser = DB::table('users')->where('id', $userId)->first();
        $this->line("   DB tenant_id: " . ($dbUser?->tenant_id ?? "NULL ⚠️"));

        // Check if issue
        if (!$user->tenant_id) {
            $this->error("\n   ❌ PROBLEM: User has no tenant_id!");
            $this->line("\n   FIX: Run:");
            $this->line("   php artisan tinker");
            $this->line("   > User::find($userId)->update(['tenant_id' => 1])");
        } else {
            // Check if tenant exists
            $tenant = Tenant::find($user->tenant_id);
            if ($tenant) {
                $this->info("\n   ✓ User has valid tenant:");
                $this->line("   - Name: {$tenant->name}");
                $this->line("   - Domain: {$tenant->domain}");
            } else {
                $this->error("\n   ❌ PROBLEM: User's tenant_id {$user->tenant_id} not found in DB!");
            }
        }

        // Check tenancy service using Tenant::current()
        $this->info("\n4. TENANCY SERVICE CHECK:");
        try {
            $currentTenant = Tenant::current();
            $this->line("   ✓ Tenant::current() available");
            $this->line("   Current tenant in context: " . ($currentTenant?->id ?? "NULL (none initialized)"));
        } catch (\Exception $e) {
            $this->error("   ❌ Tenant::current() error: " . $e->getMessage());
        }

        $this->info("\n=== DEBUG COMPLETE ===\n");
    }
}
