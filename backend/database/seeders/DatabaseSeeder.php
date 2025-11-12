<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed roles and permissions first
        $this->call(RolePermissionSeeder::class);

        // Create default super admin user
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
        ]);
        $superAdmin->assignRole('super-admin');

        // Create default admin user
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin2@example.com',
            'password' => Hash::make('password'),
        ]);
        $admin->assignRole('admin');

        // Create content manager
        $contentManager = User::create([
            'name' => 'Content Manager',
            'email' => 'content@example.com',
            'password' => Hash::make('password'),
        ]);
        $contentManager->assignRole('content-manager');

        // Create viewer
        $viewer = User::create([
            'name' => 'Viewer',
            'email' => 'viewer@example.com',
            'password' => Hash::make('password'),
        ]);
        $viewer->assignRole('viewer');

        $this->command->info('Default users created!');
        $this->command->info('Super Admin: admin@example.com / password');
        $this->command->info('Admin: admin2@example.com / password');
        $this->command->info('Content Manager: content@example.com / password');
        $this->command->info('Viewer: viewer@example.com / password');
    }
}
