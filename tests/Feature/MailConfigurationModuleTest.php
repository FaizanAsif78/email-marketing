<?php

use App\Models\MailConfiguration;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

function makeTenant(string $name = 'Acme A'): Tenant
{
    return Tenant::create([
        'company_name' => $name,
        'timezone' => 'UTC',
        'country' => 'US',
        'subscription_plan' => 'pro',
        'status' => 'active',
    ]);
}

function mailAdmin(Tenant $tenant): User
{
    $admin = User::factory()->create(['tenant_id' => $tenant->id]);
    $admin->syncRoles(['admin']);

    return $admin;
}

beforeEach(function () {
    Role::firstOrCreate(['name' => 'super-admin']);
    Role::firstOrCreate(['name' => 'admin']);
    Role::firstOrCreate(['name' => 'user']);
});

it('lets only tenant admins access the mail configuration pages', function () {
    $tenant = makeTenant();
    $admin = mailAdmin($tenant);

    $regular = User::factory()->create(['tenant_id' => $tenant->id]);
    $regular->syncRoles(['user']);

    $superAdmin = User::factory()->create();
    $superAdmin->syncRoles(['super-admin']);

    $this->actingAs($admin)->get('/mail-configurations')->assertOk();
    $this->actingAs($regular)->get('/mail-configurations')->assertForbidden();
    $this->actingAs($superAdmin)->get('/mail-configurations')->assertForbidden();
});

it('scopes mail configurations to the admins own tenant', function () {
    $tenantA = makeTenant('Acme A');
    $tenantB = makeTenant('Globex B');

    MailConfiguration::create([
        'tenant_id' => $tenantA->id,
        'smtp_host' => 'smtp.acme.com',
        'smtp_port' => 587,
        'username' => 'acme-user',
        'password' => 'secret',
        'encryption' => 'tls',
        'from_name' => 'Acme',
        'from_email' => 'noreply@acme.com',
    ]);

    MailConfiguration::create([
        'tenant_id' => $tenantB->id,
        'smtp_host' => 'smtp.globex.com',
        'smtp_port' => 465,
        'username' => 'globex-user',
        'password' => 'secret',
        'encryption' => 'ssl',
        'from_name' => 'Globex',
        'from_email' => 'noreply@globex.com',
    ]);

    $admin = mailAdmin($tenantA);

    $response = $this->actingAs($admin)->get('/mail-configurations');

    $response->assertOk()
        ->assertSee('smtp.acme.com')
        ->assertDontSee('smtp.globex.com');
});

it('creates a mail configuration scoped to the admins tenant', function () {
    $tenant = makeTenant();
    $admin = mailAdmin($tenant);

    $this->actingAs($admin)
        ->post('/mail-configurations', [
            'smtp_host' => 'smtp.example.com',
            'smtp_port' => 587,
            'username' => 'user@example.com',
            'password' => 'smtp-secret',
            'encryption' => 'tls',
            'from_name' => 'Example Co',
            'from_email' => 'noreply@example.com',
            'reply_to_email' => 'support@example.com',
            'is_default' => '1',
        ])
        ->assertRedirect(route('mail-configurations.index'));

    $configuration = MailConfiguration::where('smtp_host', 'smtp.example.com')->first();

    expect($configuration)->not->toBeNull()
        ->and($configuration->tenant_id)->toBe($tenant->id)
        ->and($configuration->is_default)->toBeTrue()
        ->and($configuration->password)->toBe('smtp-secret');
});

it('keeps the existing password when it is left blank on update', function () {
    $tenant = makeTenant();
    $admin = mailAdmin($tenant);

    $configuration = MailConfiguration::create([
        'tenant_id' => $tenant->id,
        'smtp_host' => 'smtp.example.com',
        'smtp_port' => 587,
        'username' => 'user@example.com',
        'password' => 'original-secret',
        'encryption' => 'tls',
        'from_name' => 'Example Co',
        'from_email' => 'noreply@example.com',
    ]);

    $this->actingAs($admin)
        ->put("/mail-configurations/{$configuration->id}", [
            'smtp_host' => 'smtp.example.com',
            'smtp_port' => 465,
            'username' => 'user@example.com',
            'password' => '',
            'encryption' => 'ssl',
            'from_name' => 'Example Co',
            'from_email' => 'noreply@example.com',
            'reply_to_email' => 'support@example.com',
            'is_default' => '1',
        ])
        ->assertRedirect(route('mail-configurations.index'));

    $configuration->refresh();

    expect($configuration->password)->toBe('original-secret')
        ->and($configuration->smtp_port)->toBe(465)
        ->and($configuration->encryption)->toBe('ssl');
});

it('only allows one default configuration per tenant', function () {
    $tenant = makeTenant();
    $admin = mailAdmin($tenant);

    $first = MailConfiguration::create([
        'tenant_id' => $tenant->id,
        'smtp_host' => 'smtp.first.com',
        'smtp_port' => 587,
        'username' => 'first',
        'password' => 'secret',
        'encryption' => 'tls',
        'from_name' => 'First',
        'from_email' => 'noreply@first.com',
        'is_default' => true,
    ]);

    $response = $this->actingAs($admin)
        ->post('/mail-configurations', [
            'smtp_host' => 'smtp.second.com',
            'smtp_port' => 587,
            'username' => 'second',
            'password' => 'secret',
            'encryption' => 'tls',
            'from_name' => 'Second',
            'from_email' => 'noreply@second.com',
            'is_default' => '1',
        ]);

    $response->assertRedirect(route('mail-configurations.index'));

    $first->refresh();
    $second = MailConfiguration::where('smtp_host', 'smtp.second.com')->first();

    expect($first->is_default)->toBeFalse()
        ->and($second->is_default)->toBeTrue();
});

it('does not let an admin manage another tenants configuration', function () {
    $tenantA = makeTenant('Acme A');
    $tenantB = makeTenant('Globex B');

    $configuration = MailConfiguration::create([
        'tenant_id' => $tenantB->id,
        'smtp_host' => 'smtp.globex.com',
        'smtp_port' => 587,
        'username' => 'globex-user',
        'password' => 'secret',
        'encryption' => 'tls',
        'from_name' => 'Globex',
        'from_email' => 'noreply@globex.com',
    ]);

    $admin = mailAdmin($tenantA);

    $this->actingAs($admin)
        ->get("/mail-configurations/{$configuration->id}/edit")
        ->assertNotFound();

    $this->actingAs($admin)
        ->delete("/mail-configurations/{$configuration->id}")
        ->assertNotFound();

    expect(MailConfiguration::find($configuration->id))->not->toBeNull();
});

it('deletes a mail configuration scoped to the admins tenant', function () {
    $tenant = makeTenant();
    $admin = mailAdmin($tenant);

    $configuration = MailConfiguration::create([
        'tenant_id' => $tenant->id,
        'smtp_host' => 'smtp.example.com',
        'smtp_port' => 587,
        'username' => 'user@example.com',
        'password' => 'secret',
        'encryption' => 'tls',
        'from_name' => 'Example Co',
        'from_email' => 'noreply@example.com',
    ]);

    $this->actingAs($admin)
        ->delete("/mail-configurations/{$configuration->id}")
        ->assertRedirect(route('mail-configurations.index'));

    expect(MailConfiguration::find($configuration->id))->toBeNull();
});
