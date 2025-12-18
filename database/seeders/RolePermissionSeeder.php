<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // User management
            'view users',
            'create users',
            'edit users',
            'delete users',
            'manage user roles',
            
            // Profile management
            'view profiles',
            'edit profiles',
            'delete profiles',
            'view all profiles',
            
            // Social links management
            'view social links',
            'edit social links',
            'delete social links',
            
            // Analytics
            'view analytics',
            'view reports',
            
            // Settings
            'manage settings',
            'view settings',
            
            // Roles and permissions
            'manage roles',
            'view roles',
            'assign roles',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Create roles
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $moderatorRole = Role::firstOrCreate(['name' => 'moderator', 'guard_name' => 'web']);
        $userRole = Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);

        // Assign all permissions to admin
        $adminRole->givePermissionTo(Permission::all());

        // Assign specific permissions to moderator
        $moderatorRole->givePermissionTo([
            'view users',
            'view profiles',
            'view all profiles',
            'edit profiles',
            'view social links',
            'view analytics',
            'view settings',
        ]);

        // User role has no admin permissions (default)
    }
}
