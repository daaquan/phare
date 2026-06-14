<?php

use Phalcon\Mvc\Router\Exception;
use Phare\Contracts\Debug\ExceptionHandler;

/*
 * Inertia page responses. Sending the X-Inertia header (with the current asset
 * version, so the version-mismatch guard does not fire) makes the adapter return
 * the JSON page object instead of the root HTML.
 */

$requiresDatabase = !in_array('sqlite', PDO::getAvailableDrivers(), true);

function inertiaHeaders(array $extra = []): array
{
    $manifest = dirname(__DIR__, 3) . '/public/build/manifest.json';

    return array_merge([
        'X-Inertia' => 'true',
        'X-Inertia-Version' => is_file($manifest) ? md5_file($manifest) : '',
    ], $extra);
}

test('welcome renders the Welcome Inertia component for guests', function () {
    $this->get('/', inertiaHeaders())
        ->assertOk()
        ->assertSee('"component":"Welcome"')
        ->assertSee('"user":null');
});

test('login renders the auth/Login Inertia component', function () {
    $this->get('/auth/login', inertiaHeaders())
        ->assertOk()
        ->assertSee('"component":"auth\/Login"');
});

test('dashboard renders the Dashboard Inertia component', function () {
    $this->get('/dashboard', inertiaHeaders())
        ->assertOk()
        ->assertSee('"component":"Dashboard"');
});

test('the exception handler renders the Inertia Error page for 404s', function () {
    $_SERVER['HTTP_X_INERTIA'] = 'true';
    $_SERVER['REQUEST_URI'] = '/missing';

    $handler = app(ExceptionHandler::class);
    $response = $handler->render(
        app('request'),
        new Exception('Route not found'),
    );

    expect($response->getStatusCode())->toBe(404)
        ->and($response->getContent())->toContain('"component":"Error"')
        ->and($response->getContent())->toContain('"status":404');
});

test('posts renders the Posts/Index Inertia component', function () {
    $this->get('/posts', inertiaHeaders())
        ->assertOk()
        ->assertSee('"component":"Posts\/Index"');
})->skip('Phalcon model get() segfaults under the sqlite test driver (pre-existing ORM issue, unrelated to Inertia).');
