<?php

use App\Models\User;

/*
 * Recovery codes are stored as keyed hashes (HMAC-SHA256 under APP_KEY, bound to
 * the user id); the plaintext is shown once at generation. Legacy plaintext
 * entries still verify and are upgraded in the same locked save.
 */

function hashedUser(int $id = 7): User
{
    $user = new class() extends User
    {
        public int $saves = 0;

        protected function reloadRecoveryCodes(): void
        {
            // in-memory only: no ORM in unit tests
        }

        public function save(?array $attributes = null): bool
        {
            $this->saves++;

            return true;
        }
    };
    $user->id = $id;

    return $user;
}

test('stored recovery codes contain no plaintext', function () {
    $user = hashedUser();
    $user->storeRecoveryCodes(['ABC123DEF0', 'FED9876543']);

    expect($user->two_factor_recovery_codes)->not->toContain('ABC123DEF0')
        ->and($user->two_factor_recovery_codes)->not->toContain('FED9876543')
        ->and($user->recoveryCodes())->toHaveCount(2)
        ->and($user->recoveryCodeCount())->toBe(2);
    foreach ($user->recoveryCodes() as $entry) {
        expect($entry)->toMatch('/^h1\$[0-9a-f]{64}$/');
    }
});

test('the hash is bound to the user id and the app key', function () {
    expect(User::hashRecoveryCode(7, 'ABC123DEF0'))->toBe(User::hashRecoveryCode(7, 'ABC123DEF0'))
        ->and(User::hashRecoveryCode(7, 'ABC123DEF0'))->not->toBe(User::hashRecoveryCode(8, 'ABC123DEF0'))
        ->and(User::hashRecoveryCode(7, 'ABC123DEF0'))->not->toBe(User::hashRecoveryCode(7, 'ABC123DEF1'));
});

test('hashing refuses to run without an app key', function () {
    $old = config('app.key');
    config(['app.key' => '']);
    try {
        expect(fn () => User::hashRecoveryCode(7, 'ABC123DEF0'))->toThrow(RuntimeException::class);
    } finally {
        config(['app.key' => $old]);
    }
});

test('a hashed recovery code is consumed once, case and whitespace insensitive', function () {
    $user = hashedUser();
    $user->storeRecoveryCodes(['ABC123DEF0', 'FED9876543']);

    expect($user->consumeRecoveryCode('  abc123def0 '))->toBeTrue()
        ->and($user->recoveryCodeCount())->toBe(1)
        ->and($user->two_factor_recovery_codes)->not->toContain('FED9876543')
        ->and($user->consumeRecoveryCode('ABC123DEF0'))->toBeFalse()
        ->and($user->consumeRecoveryCode('UNKNOWN'))->toBeFalse()
        ->and($user->saves)->toBe(1)
        ->and($user->consumeRecoveryCode('FED9876543'))->toBeTrue();
});

test('a stored hash is not accepted as a recovery code', function () {
    $user = hashedUser();
    $user->storeRecoveryCodes(['ABC123DEF0']);
    $stored = $user->recoveryCodes()[0];

    expect($user->consumeRecoveryCode($stored))->toBeFalse()
        ->and($user->consumeRecoveryCode(substr($stored, 3)))->toBeFalse()
        ->and($user->recoveryCodeCount())->toBe(1);
});

test('a code hashed for another user does not verify', function () {
    $other = hashedUser(8);
    $other->storeRecoveryCodes(['ABC123DEF0']);

    $user = hashedUser(7);
    $user->two_factor_recovery_codes = $other->two_factor_recovery_codes;

    expect($user->consumeRecoveryCode('ABC123DEF0'))->toBeFalse();
});

test('a legacy plaintext code still verifies and the remainder is upgraded to hashes', function () {
    $user = hashedUser();
    $user->two_factor_recovery_codes = '["ABC123DEF0","FED9876543"]';

    expect($user->consumeRecoveryCode('abc123def0'))->toBeTrue()
        ->and($user->two_factor_recovery_codes)->not->toContain('FED9876543')
        ->and($user->recoveryCodeCount())->toBe(1)
        ->and($user->consumeRecoveryCode('FED9876543'))->toBeTrue();
});

test('a wrong code leaves legacy storage untouched', function () {
    $user = hashedUser();
    $user->two_factor_recovery_codes = '["ABC123DEF0"]';

    expect($user->consumeRecoveryCode('NOPE'))->toBeFalse()
        ->and($user->two_factor_recovery_codes)->toBe('["ABC123DEF0"]')
        ->and($user->saves)->toBe(0);
});

test('upgradeLegacyRecoveryCodes hashes plaintext entries and reports whether it changed anything', function () {
    $user = hashedUser();
    $user->two_factor_recovery_codes = '["ABC123DEF0"]';

    expect($user->upgradeLegacyRecoveryCodes())->toBeTrue()
        ->and($user->two_factor_recovery_codes)->not->toContain('ABC123DEF0')
        ->and($user->consumeRecoveryCode('ABC123DEF0'))->toBeTrue();

    $again = hashedUser();
    $again->storeRecoveryCodes(['ABC123DEF0']);
    $before = $again->two_factor_recovery_codes;
    expect($again->upgradeLegacyRecoveryCodes())->toBeFalse()
        ->and($again->two_factor_recovery_codes)->toBe($before);

    expect(hashedUser()->upgradeLegacyRecoveryCodes())->toBeFalse();
});
