<?php

use App\Models\User;

test('two-factor authentication requires both a secret and confirmation', function (?string $secret, ?string $confirmed, bool $enabled) {
    $user = new User();
    $user->two_factor_secret = $secret;
    $user->two_factor_confirmed_at = $confirmed;

    expect($user->hasTwoFactorEnabled())->toBe($enabled);
})->with([
    'disabled' => [null, null, false],
    'pending confirmation' => ['ABC234', null, false],
    'missing secret' => [null, '2026-01-01 00:00:00', false],
    'enabled' => ['ABC234', '2026-01-01 00:00:00', true],
]);

test('recovery codes handle absent malformed and legacy stored values', function (?string $stored, array $expected) {
    $user = new User();
    $user->two_factor_recovery_codes = $stored;

    expect($user->recoveryCodes())->toBe($expected);
})->with([
    'absent' => [null, []],
    'invalid JSON' => ['broken', []],
    'scalar JSON' => ['"ABC"', []],
    'empty list' => ['[]', []],
    'stored codes' => ['["ABC123","DEF456"]', ['ABC123', 'DEF456']],
    'nonsequential keys and numbers' => ['{"2":"ABC123","5":123456}', ['ABC123', '123456']],
]);

test('a recovery code can be consumed only once and remaining codes are persisted', function () {
    // Exercise consumption without invoking the SQLite ORM save path.
    $user = new class() extends User
    {
        public int $saves = 0;

        protected function reloadRecoveryCodes(): void {}

        protected function persistTwoFactor(array $expected): bool
        {
            $this->saves++;

            return true;
        }
    };
    $user->id = 3;
    $user->two_factor_secret = 'SECRET';
    $user->two_factor_confirmed_at = '2026-01-01 00:00:00';
    $user->two_factor_recovery_codes = '["ABC123DEF0","FED9876543"]';

    expect($user->consumeRecoveryCode('  abc123def0  '))->toBeTrue()
        ->and($user->recoveryCodeCount())->toBe(1)
        ->and($user->two_factor_recovery_codes)->not->toContain('FED9876543') // upgraded to a hash
        ->and($user->saves)->toBe(1)
        ->and($user->consumeRecoveryCode('ABC123DEF0'))->toBeFalse()
        ->and($user->consumeRecoveryCode('UNKNOWN'))->toBeFalse()
        ->and($user->saves)->toBe(1)
        ->and($user->recoveryCodeCount())->toBe(1);
});

test('recovery codes use the expected format and support a custom count', function () {
    $codes = User::generateRecoveryCodes(3);

    expect($codes)->toHaveCount(3);
    foreach ($codes as $code) {
        expect($code)->toMatch('/^[A-F0-9]{10}$/');
    }
    expect(User::generateRecoveryCodes(0))->toBe([]);
});

test('the admin flag cannot be mass assigned', function () {
    $user = (new User())->fill(['name' => 'Mallory', 'email' => 'm@example.com', 'is_admin' => true]);

    expect($user->isAdmin())->toBeFalse()
        ->and((string)$user->name)->toBe('Mallory');
});

test('registration input cannot create an administrator', function () {
    $user = createTestUser(['is_admin' => true]);

    expect(freshUser((int)$user->id)->isAdmin())->toBeFalse();
});
