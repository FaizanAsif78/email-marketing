<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('dumps rendered create template page for inspection', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/email-templates/create');
    $html = $response->getContent();

    file_put_contents('/tmp/email-template-create.html', $html);

    foreach ([
        'grapes.min.css' => 'GRAPES CSS',
        'grapes.min.js' => 'GRAPES JS',
        'id="gjs"' => 'GJS CONTAINER',
        'grapesjs.init' => 'INIT SCRIPT',
        'gjs-one-bg' => 'CUSTOM CSS',
    ] as $needle => $label) {
        fwrite(STDERR, $label.': '.(str_contains($html, $needle) ? 'YES' : 'NO').PHP_EOL);
    }

    $this->assertTrue(true);
});