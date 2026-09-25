<?php

/*
 * Inertia page renders for the guest auth pages. Authenticated/verified flows
 * touch the User model under the pre-existing sqlite ORM segfault, so only the
 * guest-visible GET pages are asserted here.
 */

function authInertiaHeaders(array $extra = []): array
{
    $manifest = dirname(__DIR__, 3) . '/public/build/manifest.json';

    return array_merge([
        'X-Inertia' => 'true',
        'X-Inertia-Version' => is_file($manifest) ? md5_file($manifest) : '',
    ], $extra);
}

test('register page renders for guests', function () {
    $this->get('/user/register', authInertiaHeaders())
        ->assertOk()
        ->assertSee('"component":"auth\/Register"');
});

test('forgot-password page renders for guests', function () {
    $this->get('/user/forgot-password', authInertiaHeaders())
        ->assertOk()
        ->assertSee('"component":"auth\/ForgotPassword"');
});

test('reset-password page renders with the token', function () {
    $this->get('/user/reset-password/abc123?email=a@b.c', authInertiaHeaders())
        ->assertOk()
        ->assertSee('"component":"auth\/ResetPassword"')
        ->assertSee('abc123');
});

// Authenticated renders would touch the User model (sqlite ORM segfault), so
// the protected pages assert the auth middleware bounces guests to login.
dataset('protected pages', [
    '/user/verify-email',
    '/user/confirm-password',
    '/settings/profile',
    '/settings/security',
    '/settings/appearance',
]);

test('protected page redirects guests to login', function (string $uri) {
    $this->get($uri, authInertiaHeaders())
        ->assertStatus(302)
        ->assertHeader('Location', app('url')->get(route('login')));
})->with('protected pages');
