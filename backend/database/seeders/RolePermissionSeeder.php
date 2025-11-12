<?php

namespace Database\Seeders;

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
            // Users
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',
            
            // Devices (STB)
            'devices.view',
            'devices.create',
            'devices.edit',
            'devices.delete',
            'devices.manage',
            
            // Content
            'content.view',
            'content.create',
            'content.edit',
            'content.delete',
            'content.publish',
            
            // Schedules
            'schedules.view',
            'schedules.create',
            'schedules.edit',
            'schedules.delete',
            
            // Templates
            'templates.view',
            'templates.create',
            'templates.edit',
            'templates.delete',
            
            // Reports
            'reports.view',
            'reports.export',
            
            // Settings
            'settings.view',
            'settings.edit',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles and assign permissions
        
        // Super Admin - All permissions
        $superAdmin = Role::create(['name' => 'super-admin']);
        $superAdmin->givePermissionTo(Permission::all());

        // Admin - Most permissions except super admin stuff
        $admin = Role::create(['name' => 'admin']);
        $admin->givePermissionTo([
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',
            'devices.view',
            'devices.create',
            'devices.edit',
            'devices.delete',
            'devices.manage',
            'content.view',
            'content.create',
            'content.edit',
            'content.delete',
            'content.publish',
            'schedules.view',
            'schedules.create',
            'schedules.edit',
            'schedules.delete',
            'templates.view',
            'templates.create',
            'templates.edit',
            'templates.delete',
            'reports.view',
            'reports.export',
            'settings.view',
            'settings.edit',
        ]);

        // Content Manager
        $contentManager = Role::create(['name' => 'content-manager']);
        $contentManager->givePermissionTo([
            'content.view',
            'content.create',
            'content.edit',
            'content.delete',
            'content.publish',
            'schedules.view',
            'schedules.create',
            'schedules.edit',
            'schedules.delete',
            'templates.view',
            'templates.create',
            'templates.edit',
            'reports.view',
        ]);

        // Device Manager
        $deviceManager = Role::create(['name' => 'device-manager']);
        $deviceManager->givePermissionTo([
            'devices.view',
            'devices.create',
            'devices.edit',
            'devices.delete',
            'devices.manage',
            'schedules.view',
            'reports.view',
        ]);

        // Editor
        $editor = Role::create(['name' => 'editor']);
        $editor->givePermissionTo([
            'content.view',
            'content.create',
            'content.edit',
            'schedules.view',
            'templates.view',
        ]);

        // Viewer
        $viewer = Role::create(['name' => 'viewer']);
        $viewer->givePermissionTo([
            'content.view',
            'devices.view',
            'schedules.view',
            'templates.view',
            'reports.view',
        ]);

        $this->command->info('Roles and permissions created successfully!');
    }
}

