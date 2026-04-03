<?php

namespace App\Services;

use App\Models\Tenant;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Exception;

class TenantService
{
    /**
     * Create a new tenant with database and run migrations
     */
    public function createTenant(array $data): Tenant
    {
        try {
            // Create the tenant record
            $tenant = Tenant::create([
                'name' => $data['name'],
                'domain' => $data['domain'],
                'database' => $this->generateDatabaseName($data['domain']),
                'owner_name' => $data['owner_name'] ?? null,
                'owner_email' => $data['owner_email'] ?? null,
                'owner_phone' => $data['owner_phone'] ?? null,
                'industry' => $data['industry'] ?? null,
                'company_size' => $data['company_size'] ?? null,
                'address' => $data['address'] ?? null,
                'city' => $data['city'] ?? null,
                'country' => $data['country'] ?? null,
                'status' => 'active',
                'subscription_plan' => $data['subscription_plan'] ?? 'free',
                'trial_ends_at' => $data['trial_ends_at'] ?? null,
            ]);

            // Create the tenant's database
            $this->createTenantDatabase($tenant);

            // Run tenant migrations
            $this->migrateTenant($tenant);

            return $tenant;
        } catch (Exception $e) {
            // Clean up on failure
            try {
                if (isset($tenant)) {
                    Tenant::destroy($tenant->id);
                    $this->dropTenantDatabase($tenant);
                }
            } catch (Exception $cleanupError) {
                // Log cleanup errors if needed
            }
            
            throw $e;
        }
    }

    /**
     * Generate a unique database name based on domain
     */
    private function generateDatabaseName(string $domain): string
    {
        // Convert domain to valid database name
        $dbName = 'tenant_' . preg_replace('/[^a-z0-9_]/', '_', strtolower($domain));
        
        // Ensure it's not too long
        return substr($dbName, 0, 64);
    }

    /**
     * Create tenant's database
     */
    private function createTenantDatabase(Tenant $tenant): void
    {
        $connection = config('database.default');
        $dbConfig = config("database.connections.{$connection}");

        try {
            switch ($connection) {
                case 'mysql':
                    $this->createMySQLDatabase($tenant, $dbConfig);
                    break;
                case 'pgsql':
                    $this->createPostgresDatabase($tenant, $dbConfig);
                    break;
                case 'sqlite':
                    $this->createSqliteDatabase($tenant);
                    break;
                default:
                    throw new Exception("Unsupported database driver: {$connection}");
            }
        } catch (Exception $e) {
            throw new Exception("Failed to create tenant database: " . $e->getMessage());
        }
    }

    /**
     * Create MySQL database for tenant
     */
    private function createMySQLDatabase(Tenant $tenant, array $dbConfig): void
    {
        $connection = DB::connection('mysql');
        
        $connection->statement(
            "CREATE DATABASE IF NOT EXISTS `{$tenant->database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
        );
    }

    /**
     * Create PostgreSQL database for tenant
     */
    private function createPostgresDatabase(Tenant $tenant, array $dbConfig): void
    {
        $connection = DB::connection('pgsql');
        
        $connection->statement("CREATE DATABASE {$tenant->database}");
    }

    /**
     * Create SQLite database for tenant
     */
    private function createSqliteDatabase(Tenant $tenant): void
    {
        $path = database_path("tenants/{$tenant->database}.sqlite");
        
        if (!file_exists(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }
        
        touch($path);
    }

    /**
     * Run migrations for tenant database
     */
    private function migrateTenant(Tenant $tenant): void
    {
        \Spatie\Multitenancy\Facades\Tenancy::initialize($tenant);

        try {
            Artisan::call('migrate', [
                '--database' => config('multitenancy.tenant_database_connection_name'),
            ]);
        } finally {
            \Spatie\Multitenancy\Facades\Tenancy::forgetCurrent();
        }
    }

    /**
     * Delete tenant and its database
     */
    public function deleteTenant(Tenant $tenant): bool
    {
        try {
            $this->dropTenantDatabase($tenant);
            return $tenant->delete();
        } catch (Exception $e) {
            throw new Exception("Failed to delete tenant: " . $e->getMessage());
        }
    }

    /**
     * Drop tenant's database
     */
    private function dropTenantDatabase(Tenant $tenant): void
    {
        if (!$tenant) {
            return;
        }

        $connection = config('database.default');
        $dbConfig = config("database.connections.{$connection}");

        try {
            switch ($connection) {
                case 'mysql':
                    DB::connection('mysql')->statement("DROP DATABASE IF EXISTS `{$tenant->database}`");
                    break;
                case 'pgsql':
                    DB::connection('pgsql')->statement("DROP DATABASE IF EXISTS {$tenant->database}");
                    break;
                case 'sqlite':
                    $path = database_path("tenants/{$tenant->database}.sqlite");
                    if (file_exists($path)) {
                        unlink($path);
                    }
                    break;
            }
        } catch (Exception $e) {
            // Log but don't throw
        }
    }

    /**
     * Update tenant status
     */
    public function updateTenantStatus(Tenant $tenant, string $status): Tenant
    {
        $tenant->update(['status' => $status]);
        return $tenant;
    }

    /**
     * Get tenant by domain
     */
    public function getTenantByDomain(string $domain): ?Tenant
    {
        return Tenant::where('domain', $domain)->first();
    }

    /**
     * Get all active tenants
     */
    public function getActiveTenants()
    {
        return Tenant::where('status', 'active')->get();
    }
}
