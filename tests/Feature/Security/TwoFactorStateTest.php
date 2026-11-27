<?php

use App\Models\User;
use App\Support\Auth\AuthSession;
use App\Support\TwoFactor\Totp;

/*
 * Every writer of the two-factor columns (enable, confirm, regenerate, disable,
 * consume, bulk hash) goes through User::mutateTwoFactor(): per-user lock, fresh
 * read, compare-and-swap write. Model layer only (the controller write path
 * segfaults under the sqlite driver).
 */

function enabledUser(array $codes = ['AAAAAAAAAA', 'BBBBBBBBBB']): User
{
    return createTestUser([
        'two_factor_secret' => Totp::generateSecret(),
        'two_factor_recovery_codes' => json_encode($codes),
        'two_factor_confirmed_at' => '2026-01-01 00:00:00',
    ]);
}

beforeEach(function () {
    app('session')->remove(User::NEW_RECOVERY_CODES_KEY);
});

test('a stale user object cannot write the old code list over a regenerated one', function () {
    $stale = enabledUser();
    $actor = freshUser((int)$stale->id);

    $new = $actor->regenerateRecoveryCodes();
    expect($new)->toHaveCount(8);

    // stale copy still holds the old list in memory; consuming must reload first
    expect($stale->consumeRecoveryCode('AAAAAAAAAA'))->toBeFalse();

    $stored = freshUser((int)$stale->id)->recoveryCodes();
    expect($stored)->toHaveCount(8)
        ->and(json_encode($stored))->not->toContain('AAAAAAAAAA');
    expect(freshUser((int)$stale->id)->consumeRecoveryCode($new[0]))->toBeTrue();
});

test('a consume racing a disable cannot resurrect codes', function () {
    $user = enabledUser();
    freshUser((int)$user->id)->disableTwoFactor();

    expect($user->consumeRecoveryCode('AAAAAAAAAA'))->toBeFalse();
    $after = freshUser((int)$user->id);
    expect($after->two_factor_recovery_codes)->toBeNull()
        ->and($after->two_factor_secret)->toBeNull();
});

test('mutateTwoFactor aborts when the row changes between read and write', function () {
    $user = enabledUser();
    $id = (int)$user->id;

    $result = $user->mutateTwoFactor(function (User $u) use ($id): void {
        app('db')->execute('UPDATE users SET two_factor_recovery_codes = ? WHERE id = ?', ['["x"]', $id]);
        $u->storeRecoveryCodes(['ZZZZZZZZZZ']);
    });

    expect($result)->toBeFalse()
        ->and(freshUser($id)->two_factor_recovery_codes)->toBe('["x"]');
});

test('confirming a pending setup generates and stores a fresh batch', function () {
    $user = createTestUser(['two_factor_secret' => Totp::generateSecret()]);

    $codes = freshUser((int)$user->id)->confirmTwoFactor();

    $after = freshUser((int)$user->id);
    expect($codes)->toHaveCount(8)
        ->and($after->two_factor_confirmed_at)->not->toBeNull()
        ->and($after->recoveryCodeCount())->toBe(8)
        ->and($after->two_factor_recovery_codes)->not->toContain($codes[0])
        ->and($after->consumeRecoveryCode($codes[0]))->toBeTrue();
});

test('confirming replaces the legacy plaintext list a pre-upgrade setup stored', function () {
    $user = createTestUser([
        'two_factor_secret' => Totp::generateSecret(),
        'two_factor_recovery_codes' => json_encode(['LEGACYCODE1']),
    ]);

    $codes = freshUser((int)$user->id)->confirmTwoFactor();

    expect($codes)->not->toBeNull()->and(freshUser((int)$user->id)->recoveryCodeCount())->toBe(8)
        ->and(freshUser((int)$user->id)->consumeRecoveryCode('LEGACYCODE1'))->toBeFalse();
});

test('confirm is refused without a secret or when already confirmed', function () {
    $none = createTestUser();
    expect(freshUser((int)$none->id)->confirmTwoFactor())->toBeNull();

    $done = enabledUser();
    expect(freshUser((int)$done->id)->confirmTwoFactor())->toBeNull()
        ->and(freshUser((int)$done->id)->recoveryCodes())->toBe(['AAAAAAAAAA', 'BBBBBBBBBB']);
});

test('enable stores a secret and no recovery codes until confirmed', function () {
    $user = createTestUser(['two_factor_recovery_codes' => json_encode(['OLDOLDOLD1'])]);

    expect(freshUser((int)$user->id)->beginTwoFactor(Totp::generateSecret()))->toBeTrue();

    $after = freshUser((int)$user->id);
    expect($after->two_factor_secret)->not->toBeNull()
        ->and($after->two_factor_recovery_codes)->toBeNull()
        ->and($after->two_factor_confirmed_at)->toBeNull();
});

test('bulk hashing goes through the same locked path and upgrades legacy rows', function () {
    $user = enabledUser(['AAAAAAAAAA']);

    expect(freshUser((int)$user->id)->hashLegacyRecoveryCodes())->toBeTrue()
        ->and(freshUser((int)$user->id)->two_factor_recovery_codes)->not->toContain('AAAAAAAAAA')
        ->and(freshUser((int)$user->id)->hashLegacyRecoveryCodes())->toBeFalse()
        ->and(freshUser((int)$user->id)->consumeRecoveryCode('AAAAAAAAAA'))->toBeTrue();
});

test('failure logs carry a stage and user id, never the exception message', function () {
    $user = enabledUser();
    $log = tempnam(sys_get_temp_dir(), 'phare-log');
    $old = ini_get('error_log');
    ini_set('error_log', $log);
    try {
        $user->mutateTwoFactor(function (): void {
            throw new RuntimeException('SECRET-SQL-PARAM ["h1$deadbeef","PLAINCODE1"]');
        });
        $out = (string)file_get_contents($log);
    } finally {
        ini_set('error_log', (string)$old);
        @unlink($log);
    }

    expect($out)->toContain('stage=')->toContain('user=' . (int)$user->id)
        ->and($out)->not->toContain('SECRET-SQL-PARAM')->not->toContain('PLAINCODE1');
});

/*
 * Plaintext batch handed to the next security-page render.
 */

test('pending plaintext codes are bound to the user and shown once', function () {
    $a = enabledUser();
    AuthSession::flashRecoveryCodes($a, ['CODEAAAAA1']);

    expect(AuthSession::pullRecoveryCodes($a))->toBe(['CODEAAAAA1'])
        ->and(AuthSession::pullRecoveryCodes($a))->toBe([])
        ->and(app('session')->has(User::NEW_RECOVERY_CODES_KEY))->toBeFalse();
});

test('pending plaintext codes are never shown to a different user and are dropped', function () {
    $a = enabledUser();
    $b = enabledUser();
    AuthSession::flashRecoveryCodes($a, ['CODEAAAAA1']);

    expect(AuthSession::pullRecoveryCodes($b))->toBe([])
        ->and(app('session')->has(User::NEW_RECOVERY_CODES_KEY))->toBeFalse()
        ->and(AuthSession::pullRecoveryCodes($a))->toBe([]);
});

test('pending plaintext codes expire', function () {
    $a = enabledUser();
    AuthSession::flashRecoveryCodes($a, ['CODEAAAAA1']);
    $entry = app('session')->get(User::NEW_RECOVERY_CODES_KEY);
    $entry['at'] = time() - 3600;
    app('session')->set(User::NEW_RECOVERY_CODES_KEY, $entry);

    expect(AuthSession::pullRecoveryCodes($a))->toBe([])
        ->and(app('session')->has(User::NEW_RECOVERY_CODES_KEY))->toBeFalse();
});

test('a legacy unbound session value is refused', function () {
    $a = enabledUser();
    app('session')->set(User::NEW_RECOVERY_CODES_KEY, ['CODEAAAAA1']);

    expect(AuthSession::pullRecoveryCodes($a))->toBe([]);
});

test('logout and login both clear pending plaintext codes', function () {
    $a = enabledUser();
    $b = enabledUser();
    AuthSession::flashRecoveryCodes($a, ['CODEAAAAA1']);
    AuthSession::forgetPrivilegedState();
    expect(app('session')->has(User::NEW_RECOVERY_CODES_KEY))->toBeFalse();

    AuthSession::flashRecoveryCodes($a, ['CODEAAAAA1']);
    AuthSession::login($b);
    expect(app('session')->has(User::NEW_RECOVERY_CODES_KEY))->toBeFalse();
});
