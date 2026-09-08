<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'view dashboard',
            'manage users',
            'view account settings',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $superAdminRole = Role::firstOrCreate(['name' => 'super-admin']);
        $adminRole = Role::firstOrCreate(['name' => 'admin']);

        $superAdminRole->syncPermissions($permissions);
        $adminRole->syncPermissions(['view dashboard', 'view account settings']);

        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@gmail.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
            ]
        );
        $superAdmin->assignRole('super-admin');

        $rehman = User::firstOrCreate(
            ['email' => 'rehman@gmail.com'],
            [
                'name' => 'Rehman',
                'password' => Hash::make('password'),
            ]
        );
        $rehman->assignRole('admin');

        $abdullah = User::firstOrCreate(
            ['email' => 'abdullah@gmail.com'],
            [
                'name' => 'Abdullah',
                'password' => Hash::make('password'),
            ]
        );
        $abdullah->assignRole('admin');
    }
}
