<?php

test('security write endpoints redirect guests before changing data', function (string $method, string $uri) {
    $this->call($method, $uri)
        ->assertStatus(302)
        ->assertHeader('Location', app('url')->get(route('login')));
})->with([
    'update profile' => ['PATCH', '/settings/profile'],
    'delete account' => ['DELETE', '/settings/profile'],
    'update password' => ['PUT', '/settings/password'],
    'enable two-factor' => ['POST', '/settings/two-factor/enable'],
    'confirm two-factor' => ['POST', '/settings/two-factor/confirm'],
    'regenerate recovery codes' => ['POST', '/settings/two-factor/recovery-codes'],
    'disable two-factor' => ['DELETE', '/settings/two-factor'],
    'passkey registration options' => ['POST', '/settings/passkeys/options'],
    'register passkey' => ['POST', '/settings/passkeys'],
    'delete passkey' => ['DELETE', '/settings/passkeys/1'],
    'broadcast monitor' => ['GET', '/admin/broadcasting'],
    'broadcast channels' => ['GET', '/admin/broadcasting/channels'],
    'broadcast channel details' => ['GET', '/admin/broadcasting/channel'],
    'send broadcast' => ['POST', '/admin/broadcasting/test'],
]);

test('two-factor challenge requires a pending password login', function (string $method) {
    app('session')->remove('login.id');

    $this->call($method, '/user/two-factor-challenge')
        ->assertStatus(302)
        ->assertHeader('Location', app('url')->get('/user/login'));
})->with(['GET', 'POST']);

test('passkey login rejects a missing challenge', function () {
    app('session')->remove('passkey.request');

    $this->post('/user/passkeys/login')
        ->assertStatus(422)
        ->assertSee('Your session expired. Please try again.');
});

test('passkey login consumes the challenge even when the request is malformed', function () {
    app('session')->set('passkey.request', '{}');

    try {
        $this->post('/user/passkeys/login')
            ->assertStatus(422)
            ->assertSee('Malformed request.');

        expect(app('session')->has('passkey.request'))->toBeFalse();

        $this->post('/user/passkeys/login')
            ->assertStatus(422)
            ->assertSee('Your session expired. Please try again.');
    } finally {
        app('session')->remove('passkey.request');
    }
});
