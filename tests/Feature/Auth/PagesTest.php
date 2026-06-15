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

// Route middleware (auth/guest/verified) runs via Phalcon application events in
// production but is a no-op in the test harness (same as the #3 dashboard test),
// so these assert the rendered component rather than the redirect.
test('verify-email page renders the VerifyEmail component', function () {
    $this->get('/user/verify-email', authInertiaHeaders())
        ->assertOk()
        ->assertSee('"component":"auth\/VerifyEmail"');
});

test('settings profile renders the Profile component', function () {
    $this->get('/settings/profile', authInertiaHeaders())
        ->assertOk()
        ->assertSee('"component":"settings\/Profile"');
});

test('settings password renders the Password component', function () {
    $this->get('/settings/password', authInertiaHeaders())
        ->assertOk()
        ->assertSee('"component":"settings\/Password"');
});

test('settings appearance renders the Appearance component', function () {
    $this->get('/settings/appearance', authInertiaHeaders())
        ->assertOk()
        ->assertSee('"component":"settings\/Appearance"');
});

test('confirm-password page renders the ConfirmPassword component', function () {
    $this->get('/user/confirm-password', authInertiaHeaders())
        ->assertOk()
        ->assertSee('"component":"auth\/ConfirmPassword"');
});

test('settings two-factor renders the TwoFactor component', function () {
    $this->get('/settings/two-factor', authInertiaHeaders())
        ->assertOk()
        ->assertSee('"component":"settings\/TwoFactor"');
});
