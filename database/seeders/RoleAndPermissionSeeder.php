<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()['cache']->forget('spatie.permission.cache');

        // Create permissions
        $permissions = [
            'view-applicants',
            'delete-jobs',
            'change-settings',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Create roles and assign permissions
        
        // Admin role - has all permissions
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $adminRole->syncPermissions($permissions);

        // Recruiter role - can only view applicants
        $recruiterRole = Role::firstOrCreate(['name' => 'recruiter', 'guard_name' => 'web']);
        $recruiterRole->syncPermissions(['view-applicants']);

        // User role (job seeker) - no special permissions
        Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);
    }
}
