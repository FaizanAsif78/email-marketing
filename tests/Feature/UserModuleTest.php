<?php

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    $superAdminRole = Role::firstOrCreate(['name' => 'super-admin']);
    $adminRole = Role::firstOrCreate(['name' => 'admin']);
    $userRole = Role::firstOrCreate(['name' => 'user']);

    foreach (['view dashboard', 'manage users', 'manage tenants', 'view account settings'] as $permission) {
        Permission::firstOrCreate(['name' => $permission]);
    }
    $superAdminRole->syncPermissions(['view dashboard', 'manage users', 'manage tenants', 'view account settings']);
    $adminRole->syncPermissions(['view dashboard', 'view account settings']);
    $userRole->syncPermissions(['view dashboard', 'view account settings']);
});

it('lets only super-admins access the tenant pages', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->syncRoles(['super-admin']);

    $admin = User::factory()->create();
    $admin->syncRoles(['admin']);

    $this->actingAs($superAdmin)->get('/tenants')->assertOk();
    $this->actingAs($admin)->get('/tenants')->assertForbidden();
});

it('lets super-admins and admins access users, but not regular users', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->syncRoles(['super-admin']);

    $admin = User::factory()->create();
    $admin->syncRoles(['admin']);

    $regular = User::factory()->create();
    $regular->syncRoles(['user']);

    $this->actingAs($superAdmin)->get('/users')->assertOk();
    $this->actingAs($admin)->get('/users')->assertOk();
    $this->actingAs($regular)->get('/users')->assertForbidden();
});

it('scopes the user list to the admins own tenant', function () {
    $tenantA = Tenant::create([
        'company_name' => 'Acme A',
        'timezone' => 'UTC',
        'country' => 'US',
        'subscription_plan' => 'pro',
        'status' => 'active',
    ]);
    $tenantB = Tenant::create([
        'company_name' => 'Globex B',
        'timezone' => 'UTC',
        'country' => 'PK',
        'subscription_plan' => 'pro',
        'status' => 'active',
    ]);

    $admin = User::factory()->create(['tenant_id' => $tenantA->id]);
    $admin->syncRoles(['admin']);

    $inTenant = User::factory()->create(['name' => 'In Tenant', 'tenant_id' => $tenantA->id]);
    $inTenant->syncRoles(['user']);

    $otherTenant = User::factory()->create(['name' => 'Other Tenant', 'tenant_id' => $tenantB->id]);
    $otherTenant->syncRoles(['user']);

    $response = $this->actingAs($admin)->get('/users');

    $response->assertOk()
        ->assertSee('In Tenant')
        ->assertDontSee('Other Tenant');
});

it('rejects a second admin for the same tenant', function () {
    $tenant = Tenant::create([
        'company_name' => 'Acme A',
        'timezone' => 'UTC',
        'country' => 'US',
        'subscription_plan' => 'pro',
        'status' => 'active',
    ]);

    $superAdmin = User::factory()->create();
    $superAdmin->syncRoles(['super-admin']);

    $existingAdmin = User::factory()->create(['name' => 'First Admin', 'tenant_id' => $tenant->id]);
    $existingAdmin->syncRoles(['admin']);

    $this->actingAs($superAdmin)
        ->post('/users', [
            'name' => 'Second Admin',
            'email' => 'second@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'admin',
            'tenant_id' => $tenant->id,
        ])
        ->assertSessionHasErrors('role');

    expect(User::where('email', 'second@example.com')->count())->toBe(0);
});

it('creates the tenant admin when a super-admin creates a tenant', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->syncRoles(['super-admin']);

    $this->actingAs($superAdmin)
        ->post('/tenants', [
            'company_name' => 'Brand New Co',
            'website' => 'https://brandnew.example',
            'timezone' => 'UTC',
            'country' => 'AU',
            'subscription_plan' => 'basic',
            'status' => 'active',
            'admin_name' => 'Bob Admin',
            'admin_email' => 'bob@brandnew.example',
            'admin_password' => 'password123',
        ])
        ->assertRedirect(route('tenants.index'));

    $tenant = Tenant::where('company_name', 'Brand New Co')->first();

    expect($tenant)->not->toBeNull();

    $admin = User::where('email', 'bob@brandnew.example')->first();

    expect($admin)->not->toBeNull()
        ->and($admin->tenant_id)->toBe($tenant->id)
        ->and($admin->hasRole('admin'))->toBeTrue();
});

it('assigns a tenant when a super-admin creates a user and syncs the role', function () {
    $tenant = Tenant::create([
        'company_name' => 'Acme A',
        'timezone' => 'UTC',
        'country' => 'US',
        'subscription_plan' => 'pro',
        'status' => 'active',
    ]);

    $superAdmin = User::factory()->create();
    $superAdmin->syncRoles(['super-admin']);

    $this->actingAs($superAdmin)
        ->post('/users', [
            'name' => 'New Guy',
            'email' => 'newguy@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'user',
            'tenant_id' => $tenant->id,
        ])
        ->assertRedirect(route('users.index'));

    $user = User::where('email', 'newguy@example.com')->first();

    expect($user)->not->toBeNull()
        ->and($user->tenant_id)->toBe($tenant->id)
        ->and($user->hasRole('user'))->toBeTrue();
});
