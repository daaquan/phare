<?php

use App\Support\Auth\AuthSession;

/*
 * Logging out must not leave privileges for the next person on the browser,
 * and changing the password must kill outstanding reset links and rotate the
 * session id.
 */

function seedPrivilegedSession(): void
{
    $session = app('session');
    $session->set('auth.password_confirmed_at', time());
    $session->set('login.id', 999);
    $session->set('url.intended', '/settings/security');
    $session->set('passkey.creation', '{}');
    $session->set('passkey.request', '{}');
}

function privilegedKeysLeft(): array
{
    return array_values(array_filter(AuthSession::PRIVILEGED_KEYS, fn (string $key) => app('session')->has($key)));
}

test('logout clears privileged session state and rotates the session id', function (string $uri) {
    signIn(createTestUser());
    seedPrivilegedSession();
    $before = session_id();

    $this->post($uri);

    expect(privilegedKeysLeft())->toBe([])
        ->and(app('session')->has((string)config('auth.session_id', 'auth')))->toBeFalse()
        ->and(session_id())->not->toBe($before);

    // The next user of the browser gets no password-confirmation grace period.
    AuthSession::login(createTestUser()); // not signIn(): it clears the keys itself
    $this->delete('/settings/two-factor')
        ->assertStatus(302)
        ->assertHeader('Location', app('url')->get('/user/confirm-password'));
})->with(['web logout' => ['/user/logout'], 'API logout' => ['/api/auth/logout']]);

test('changing the password invalidates reset tokens and rotates the session id', function () {
    $user = signIn(createTestUser());
    $token = app('password.broker')->createToken($user);
    $before = session_id();

    $this->put('/settings/password', [
        'current_password' => 'secret123',
        'password' => 'brand-new-pass',
        'password_confirmation' => 'brand-new-pass',
    ])
        ->assertStatus(302)
        ->assertHeader('Location', app('url')->get('/settings/security'));

    expect(password_verify('brand-new-pass', (string)freshUser((int)$user->id)->password))->toBeTrue()
        ->and(app('password.broker')->validateToken((string)$user->email, $token))->toBeFalse()
        ->and(session_id())->not->toBe($before)
        ->and(app('session')->has((string)config('auth.session_id', 'auth')))->toBeTrue();
});

test('a wrong current password changes nothing', function () {
    $user = signIn(createTestUser());
    $token = app('password.broker')->createToken($user);

    $this->put('/settings/password', [
        'current_password' => 'not-my-password',
        'password' => 'brand-new-pass',
        'password_confirmation' => 'brand-new-pass',
    ])->assertStatus(302);

    expect(password_verify('secret123', (string)freshUser((int)$user->id)->password))->toBeTrue()
        ->and(app('password.broker')->validateToken((string)$user->email, $token))->toBeTrue();
});
