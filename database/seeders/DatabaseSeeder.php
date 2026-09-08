<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'view dashboard',
            'manage users',
            'view account settings',
            'manage tenants',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $superAdminRole = Role::firstOrCreate(['name' => 'super-admin']);
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $userRole = Role::firstOrCreate(['name' => 'user']);

        $superAdminRole->syncPermissions($permissions);
        $adminRole->syncPermissions(['view dashboard', 'view account settings']);
        $userRole->syncPermissions(['view dashboard', 'view account settings']);

        $superAdmin = User::updateOrCreate(
            ['email' => 'superadmin@gmail.com'],
            [
                'name' => 'Super Admin',
                'password' => 'password',
                'tenant_id' => null,
            ]
        );
        $superAdmin->syncRoles(['super-admin']);

        $acme = Tenant::updateOrCreate(['company_name' => 'Acme Corp'], [
            'timezone' => 'America/New_York',
            'country' => 'United States',
            'subscription_plan' => 'enterprise',
            'status' => 'active',
        ]);

        $globex = Tenant::updateOrCreate(['company_name' => 'Globex Ltd'], [
            'timezone' => 'Asia/Karachi',
            'country' => 'Pakistan',
            'subscription_plan' => 'pro',
            'status' => 'active',
        ]);

        $abdullah = User::updateOrCreate(
            ['email' => 'abdullah@gmail.com'],
            [
                'name' => 'Abdullah',
                'password' => 'password',
                'tenant_id' => $acme->id,
            ]
        );
        $abdullah->syncRoles(['admin']);

        $rehman = User::updateOrCreate(
            ['email' => 'rehman@gmail.com'],
            [
                'name' => 'Rehman',
                'password' => 'password',
                'tenant_id' => $globex->id,
            ]
        );
        $rehman->syncRoles(['admin']);

        $john = User::updateOrCreate(
            ['email' => 'john@acme.com'],
            [
                'name' => 'John Doe',
                'password' => 'password',
                'tenant_id' => $acme->id,
            ]
        );
        $john->syncRoles(['user']);

        $jane = User::updateOrCreate(
            ['email' => 'jane@globex.com'],
            [
                'name' => 'Jane Smith',
                'password' => 'password',
                'tenant_id' => $globex->id,
            ]
        );
        $jane->syncRoles(['user']);
    }
}
