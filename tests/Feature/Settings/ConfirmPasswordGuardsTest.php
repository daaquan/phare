<?php

use App\Models\User;
use App\Support\Auth\AuthSession;
use App\Support\TwoFactor\Totp;
use Phalcon\Mvc\Router\Exception;

/*
 * A stolen session alone must not be enough to take an account over: changing
 * the email, registering a passkey and reading recovery codes all require a
 * recent password confirmation.
 */

const JSON_HEADERS = ['Accept' => 'application/json', 'X-Requested-With' => 'XMLHttpRequest'];

function confirmPasswordNow(): void
{
    app('session')->set('auth.password_confirmed_at', time());
}

function twoFactorEnabledUser(): User
{
    return createTestUser([
        'two_factor_secret' => Totp::generateSecret(),
        'two_factor_recovery_codes' => json_encode(['AAAAA11111', 'BBBBB22222']),
        'two_factor_confirmed_at' => '2026-01-01 00:00:00',
    ]);
}

test('changing the email requires a password confirmation', function () {
    $user = signIn(createTestUser(['email' => 'owner@example.com']));

    $this->patch('/settings/profile', ['name' => 'Owner', 'email' => 'attacker@example.com'])
        ->assertStatus(302)
        ->assertHeader('Location', app('url')->get('/user/confirm-password'));

    expect((string)freshUser((int)$user->id)->email)->toBe('owner@example.com');
});

test('a confirmed password lets the email change go through', function () {
    $user = signIn(createTestUser(['email' => 'before@example.com']));
    confirmPasswordNow();

    $this->patch('/settings/profile', ['name' => 'Owner', 'email' => 'after@example.com'])
        ->assertStatus(302)
        ->assertHeader('Location', app('url')->get('/settings/profile'));

    $fresh = freshUser((int)$user->id);
    expect((string)$fresh->email)->toBe('after@example.com')
        ->and($fresh->hasVerifiedEmail())->toBeFalse();
});

test('changing the email to an address already in use is refused', function () {
    createTestUser(['email' => 'taken@example.com']);
    $user = signIn(createTestUser(['email' => 'mine@example.com']));
    confirmPasswordNow();

    $this->patch('/settings/profile', ['name' => 'Owner', 'email' => 'taken@example.com'])
        ->assertStatus(302);

    expect((string)freshUser((int)$user->id)->email)->toBe('mine@example.com')
        ->and(app('session')->get('errors'))->toHaveKey('email');
});

test('passkey registration endpoints require a password confirmation', function (string $uri) {
    signIn(createTestUser());

    $this->post($uri, [], JSON_HEADERS)
        ->assertStatus(423)
        ->assertSee('"redirect":"\/user\/confirm-password"');
})->with(['options' => ['/settings/passkeys/options'], 'register' => ['/settings/passkeys']]);

test('passkey registration options are issued after a password confirmation', function () {
    signIn(createTestUser());
    confirmPasswordNow();

    $this->post('/settings/passkeys/options', [], JSON_HEADERS)
        ->assertOk()
        ->assertSee('"challenge"');
});

test('the security page no longer ships recovery codes', function () {
    signIn(twoFactorEnabledUser());
    confirmPasswordNow();

    $response = $this->get('/settings/security', ['X-Inertia' => 'true'])->assertOk();

    expect($response->getContent())->not->toContain('AAAAA11111')
        ->and($response->getContent())->not->toContain('recoveryCodes')
        ->and($response->getContent())->toContain('"enabled":true');
});

test('stored recovery codes can no longer be revealed', function () {
    signIn(twoFactorEnabledUser());
    confirmPasswordNow();

    expect(fn () => $this->post('/settings/two-factor/recovery-codes/reveal', [], JSON_HEADERS))
        ->toThrow(Exception::class);
});

test('freshly generated recovery codes are shown once and then dropped', function () {
    $user = signIn(twoFactorEnabledUser());
    AuthSession::flashRecoveryCodes($user, ['NEWCODE111', 'NEWCODE222']);

    $first = $this->get('/settings/security', ['X-Inertia' => 'true'])->assertOk()->getContent();
    $second = $this->get('/settings/security', ['X-Inertia' => 'true'])->assertOk()->getContent();

    expect($first)->toContain('NEWCODE111')->toContain('"recoveryCodeCount":2')
        ->and($second)->not->toContain('NEWCODE111')
        ->and(app('session')->has(User::NEW_RECOVERY_CODES_KEY))->toBeFalse();
});
