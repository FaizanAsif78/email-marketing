<?php

use App\Jobs\SendBulkEmailBatch;
use App\Models\BulkMailing;
use App\Models\EmailTemplate;
use App\Models\MailConfiguration;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

function makeBulkTenant(string $name = 'Acme A'): Tenant
{
    return Tenant::create([
        'company_name' => $name,
        'timezone' => 'UTC',
        'country' => 'US',
        'subscription_plan' => 'pro',
        'status' => 'active',
    ]);
}

function bulkOwner(Tenant $tenant): User
{
    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    $user->syncRoles(['user']);

    return $user;
}

function bulkConfig(Tenant $tenant): MailConfiguration
{
    return MailConfiguration::create([
        'tenant_id' => $tenant->id,
        'smtp_host' => 'smtp.acme.com',
        'smtp_port' => 587,
        'username' => 'acme-user',
        'password' => 'secret',
        'encryption' => 'tls',
        'from_name' => 'Acme',
        'from_email' => 'noreply@acme.com',
    ]);
}

beforeEach(function () {
    Role::firstOrCreate(['name' => 'super-admin']);
    Role::firstOrCreate(['name' => 'admin']);
    Role::firstOrCreate(['name' => 'user']);
});

it('queues one batch job per 10 recipients instead of sending inline', function () {
    $tenant = makeBulkTenant();
    $user = bulkOwner($tenant);
    $config = bulkConfig($tenant);
    EmailTemplate::create([
        'user_id' => $user->id,
        'name' => 'Welcome',
        'subject' => 'Hi {first_name}',
        'content' => 'Hello {first_name}, thanks for joining!',
    ]);

    Bus::fake();

    $recipients = [];
    for ($i = 0; $i < 23; $i++) {
        $recipients[] = "user{$i}@example.com";
    }

    $this->actingAs($user)->postJson('/bulk-mail/send', [
        'mail_configuration_id' => $config->id,
        'email_template_id' => EmailTemplate::first()->id,
        'recipients' => $recipients,
    ])
        ->assertOk()
        ->assertJson([
            'queued' => true,
            'total' => 23,
            'batches' => 3,
        ]);

    Bus::assertBatched(function (Illuminate\Bus\PendingBatch $batch) {
        return $batch->jobs->count() === 3
            && $batch->jobs->every(fn ($job) => $job instanceof SendBulkEmailBatch);
    });

    $mailing = BulkMailing::where('user_id', $user->id)->first();

    expect($mailing)->not->toBeNull();
    expect($mailing->recipients_count)->toBe(23);
    expect($mailing->status)->toBe(BulkMailing::STATUS_PROCESSING);
    expect($mailing->job_batch_id)->toBeString();
});

it('reports batch status to the frontend once finished', function () {
    $tenant = makeBulkTenant();
    $user = bulkOwner($tenant);

    BulkMailing::create([
        'user_id' => $user->id,
        'tenant_id' => $tenant->id,
        'recipients' => ['a@example.com'],
        'recipients_count' => 1,
        'sent_count' => 1,
        'failed_count' => 0,
        'status' => BulkMailing::STATUS_COMPLETED,
        'completed_at' => now(),
        'job_batch_id' => 'batch-test-1',
    ]);

    $this->actingAs($user)->getJson('/bulk-mail/batch-status?batch_id=batch-test-1')
        ->assertOk()
        ->assertJson([
            'finished' => true,
            'status' => BulkMailing::STATUS_COMPLETED,
            'total' => 1,
            'sent' => 1,
            'failed' => 0,
        ]);

    $this->actingAs($user)->getJson('/bulk-mail/batch-status?batch_id=not-yours')
        ->assertNotFound();
});