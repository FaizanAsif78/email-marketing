<?php

use App\Models\EmailTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    foreach (['super-admin', 'admin', 'user'] as $role) {
        Role::firstOrCreate(['name' => $role]);
    }
});

function templateUser(array $attributes = []): User
{
    $user = User::factory()->create($attributes);
    $user->syncRoles(['user']);

    return $user;
}

function makeTemplate(User $user, array $attributes = []): EmailTemplate
{
    return EmailTemplate::create(array_merge([
        'user_id' => $user->id,
        'name' => 'Welcome Email',
        'subject' => 'Welcome!',
        'content' => '<!DOCTYPE html><html><body><h1>Hello</h1></body></html>',
    ], $attributes));
}

it('requires authentication for the template pages', function () {
    $this->get('/email-templates')->assertRedirect(route('login'));
    $this->get('/email-templates/create')->assertRedirect(route('login'));
});

it('lets any authenticated user manage their own templates', function () {
    $user = templateUser();

    $this->actingAs($user)->get('/email-templates')->assertOk();
    $this->actingAs($user)->get('/email-templates/create')->assertOk();
});

it('scopes templates to the current user', function () {
    $user = templateUser();
    $other = templateUser();

    makeTemplate($user, ['name' => 'Mine']);
    makeTemplate($other, ['name' => 'Not Mine']);

    $this->actingAs($user)
        ->get('/email-templates')
        ->assertOk()
        ->assertSee('Mine')
        ->assertDontSee('Not Mine');
});

it('stores the authenticated user id when creating a template', function () {
    $user = templateUser();

    $this->actingAs($user)
        ->post('/email-templates', [
            'name' => 'Newsletter',
            'subject' => 'Monthly update',
            'content' => '<!DOCTYPE html><html><body><p>Content</p></body></html>',
        ])
        ->assertRedirect(route('email-templates.index'));

    $template = EmailTemplate::where('name', 'Newsletter')->first();

    expect($template)->not->toBeNull()
        ->and($template->user_id)->toBe($user->id)
        ->and($template->subject)->toBe('Monthly update');
});

it('validates required fields on create', function () {
    $user = templateUser();

    $this->actingAs($user)
        ->post('/email-templates', [])
        ->assertSessionHasErrors(['name', 'content']);
});

it('lets a user update their own template', function () {
    $user = templateUser();
    $template = makeTemplate($user);

    $this->actingAs($user)
        ->put("/email-templates/{$template->id}", [
            'name' => 'Renamed',
            'subject' => 'New subject',
            'content' => '<!DOCTYPE html><html><body><p>Updated</p></body></html>',
        ])
        ->assertRedirect(route('email-templates.index'));

    expect($template->refresh()->name)->toBe('Renamed');
});

it('does not let a user manage another users template', function () {
    $user = templateUser();
    $other = templateUser();
    $template = makeTemplate($other);

    $this->actingAs($user)
        ->get("/email-templates/{$template->id}/edit")
        ->assertNotFound();

    $this->actingAs($user)
        ->put("/email-templates/{$template->id}", [
            'name' => 'Hacked',
            'content' => '<p>Hacked</p>',
        ])
        ->assertNotFound();

    $this->actingAs($user)
        ->delete("/email-templates/{$template->id}")
        ->assertNotFound();

    expect(EmailTemplate::find($template->id))->not->toBeNull();
});

it('lets a user delete their own template', function () {
    $user = templateUser();
    $template = makeTemplate($user);

    $this->actingAs($user)
        ->delete("/email-templates/{$template->id}")
        ->assertRedirect(route('email-templates.index'));

    expect(EmailTemplate::find($template->id))->toBeNull();
});
