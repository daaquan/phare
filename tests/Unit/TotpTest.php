<?php

use App\Models\User;
use App\Support\TwoFactor\Totp;

// RFC 6238 test vector: the ASCII secret "12345678901234567890" is
// Base32 "GEZDGNBVGY3TQOJQGEZDGNBVGY3TQOJQ". At t=59 the 8-digit code is
// 94287082, whose last 6 digits are 287082.
const RFC_SECRET = 'GEZDGNBVGY3TQOJQGEZDGNBVGY3TQOJQ';

test('codeAt matches the RFC 6238 test vector', function () {
    expect(Totp::codeAt(RFC_SECRET, 59))->toBe('287082');
});

test('generateSecret returns a 16-char base32 string', function () {
    $secret = Totp::generateSecret();

    expect($secret)->toHaveLength(16)
        ->and($secret)->toMatch('/^[A-Z2-7]+$/');
});

test('verify accepts the current code and rejects a wrong one', function () {
    $secret = Totp::generateSecret();
    $valid = Totp::codeAt($secret, time());

    expect(Totp::verify($secret, $valid))->toBeTrue()
        ->and(Totp::verify($secret, '000000'))->toBeFalse()
        ->and(Totp::verify($secret, 'abc'))->toBeFalse();
});

test('provisioningUri embeds the secret and issuer', function () {
    $uri = Totp::provisioningUri('ABC234', 'user@example.com', 'Phare');

    expect($uri)->toStartWith('otpauth://totp/')
        ->and($uri)->toContain('secret=ABC234')
        ->and($uri)->toContain('issuer=Phare');
});

test('generateRecoveryCodes returns the requested count of unique codes', function () {
    $codes = User::generateRecoveryCodes(8);

    expect($codes)->toHaveCount(8)
        ->and(array_unique($codes))->toHaveCount(8);
});
