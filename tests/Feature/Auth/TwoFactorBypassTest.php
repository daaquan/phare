<?php

use App\Models\User;
use App\Support\TwoFactor\Totp;

/*
 * A correct password alone must never yield an authenticated session for an
 * account with two-factor enabled: not through the JSON API login, and not
 * through a completed password reset.
 */

function twoFactorUser(): User
{
    return createTestUser([
        'two_factor_secret' => Totp::generateSecret(),
        'two_factor_recovery_codes' => json_encode(['ABCDEF1234']),
        'two_factor_confirmed_at' => '2026-01-01 00:00:00',
    ]);
}

function sessionIsAuthenticated(): bool
{
    return app('session')->has((string)config('auth.session_id', 'auth'));
}

test('API login refuses to open a session for a two-factor account', function () {
    $user = twoFactorUser();

    $this->post('/api/auth/login', ['email' => $user->email, 'password' => 'secret123'])
        ->assertStatus(403)
        ->assertSee('"two_factor":true');

    expect(sessionIsAuthenticated())->toBeFalse();

    $this->get('/settings/profile')
        ->assertStatus(302)
        ->assertHeader('Location', app('url')->get(route('login')));
});

test('API login still signs in accounts without two-factor', function () {
    $user = createTestUser();

    $this->post('/api/auth/login', ['email' => $user->email, 'password' => 'secret123'])
        ->assertOk()
        ->assertSee(ID::encode($user->id));

    expect(sessionIsAuthenticated())->toBeTrue();
});

test('API login with a wrong password stays unauthorized for two-factor accounts', function () {
    $user = twoFactorUser();

    $this->post('/api/auth/login', ['email' => $user->email, 'password' => 'wrong-password'])
        ->assertStatus(401);

    expect(sessionIsAuthenticated())->toBeFalse();
});

test('web login holds a two-factor account at the challenge without authenticating', function () {
    $user = twoFactorUser();

    $this->post('/user/login', ['email' => $user->email, 'password' => 'secret123'])
        ->assertStatus(302)
        ->assertHeader('Location', app('url')->get('/user/two-factor-challenge'));

    expect(sessionIsAuthenticated())->toBeFalse()
        ->and((int)app('session')->get('login.id'))->toBe((int)$user->id);
});

test('a completed password reset does not sign in and leaves two-factor in force', function (bool $twoFactor) {
    $user = $twoFactor ? twoFactorUser() : createTestUser();
    $token = app('password.broker')->createToken($user);

    $this->post('/user/reset-password', [
        'email' => $user->email,
        'token' => $token,
        'password' => 'new-password-123',
        'password_confirmation' => 'new-password-123',
    ])
        ->assertStatus(302)
        ->assertHeader('Location', app('url')->get('/user/login'));

    expect(sessionIsAuthenticated())->toBeFalse()
        ->and(password_verify('new-password-123', (string)freshUser((int)$user->id)->password))->toBeTrue()
        ->and(app('password.broker')->validateToken((string)$user->email, $token))->toBeFalse();

    if ($twoFactor) {
        // The new password leads to the challenge, not to a session.
        $this->post('/user/login', ['email' => $user->email, 'password' => 'new-password-123'])
            ->assertHeader('Location', app('url')->get('/user/two-factor-challenge'));
        expect(sessionIsAuthenticated())->toBeFalse();
    }
})->with(['two-factor account' => [true], 'plain account' => [false]]);
