<?php

use App\Models\User;
use App\Support\Auth\AuthSession;
use App\Support\TwoFactor\Totp;

/*
 * A password-verified login waiting at the two-factor challenge expires: a
 * stale, unattended browser cannot be finished later with just a code.
 */

function pendingUser(): User
{
    $secret = Totp::generateSecret();

    return createTestUser([
        'two_factor_secret' => $secret,
        'two_factor_recovery_codes' => json_encode(['ABCDEF1234']),
        'two_factor_confirmed_at' => '2026-01-01 00:00:00',
    ]);
}

beforeEach(function () {
    foreach (['login.id', 'login.at'] as $key) {
        app('session')->remove($key);
    }
});

test('a fresh pending login is returned by pendingTwoFactorId', function () {
    $user = pendingUser();
    AuthSession::beginTwoFactor($user);

    expect(AuthSession::pendingTwoFactorId())->toBe((int)$user->id);
});

test('an expired or unstamped pending login is dropped', function (?int $age) {
    $user = pendingUser();
    app('session')->set('login.id', $user->id);
    if ($age !== null) {
        app('session')->set('login.at', time() - $age);
    }

    expect(AuthSession::pendingTwoFactorId())->toBeNull()
        ->and(app('session')->has('login.id'))->toBeFalse()
        ->and(app('session')->has('login.at'))->toBeFalse();
})->with(['too old' => [301], 'in the future' => [-60], 'no start time' => [null]]);

test('the challenge refuses a recovery code once the pending login expired', function () {
    $user = pendingUser();
    app('session')->set('login.id', $user->id);
    app('session')->set('login.at', time() - 3600);

    $this->post('/user/two-factor-challenge', ['recovery_code' => 'ABCDEF1234'])
        ->assertStatus(302)
        ->assertHeader('Location', app('url')->get('/user/login'));

    expect(app('session')->has((string)config('auth.session_id', 'auth')))->toBeFalse()
        ->and(freshUser((int)$user->id)->recoveryCodes())->toBe(['ABCDEF1234']);
});

test('the challenge still accepts a recovery code while the pending login is fresh', function () {
    $user = pendingUser();
    AuthSession::beginTwoFactor($user);

    $this->post('/user/two-factor-challenge', ['recovery_code' => 'ABCDEF1234'])
        ->assertStatus(302)
        ->assertHeader('Location', app('url')->get('/dashboard'));

    expect(app('session')->has((string)config('auth.session_id', 'auth')))->toBeTrue()
        ->and(app('session')->has('login.at'))->toBeFalse();
});
