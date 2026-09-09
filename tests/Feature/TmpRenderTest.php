<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('renders the create template page with TinyMCE', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/email-templates/create');
    $html = $response->getContent();

    file_put_contents('/tmp/email-template-create.html', $html);

    foreach ([
        'assets/vendor/tinymce/tinymce.min.js' => 'TINYMCE JS',
        'id="email-content"' => 'EDITOR TEXTAREA',
        'tinymce.init' => 'INIT SCRIPT',
        'placeholder-chips' => 'PLACEHOLDER CHIPS',
        'data-insert-text' => 'INSERT BUTTONS',
    ] as $needle => $label) {
        fwrite(STDERR, $label.': '.(str_contains($html, $needle) ? 'YES' : 'NO').PHP_EOL);
    }

    $this->assertTrue(true);
});
