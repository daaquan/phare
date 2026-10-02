<?php

use App\Models\User;
use App\Support\TwoFactor\Totp;

// RFC 6238 test vector: the ASCII secret "12345678901234567890" is
// Base32 "GEZDGNBVGY3TQOJQGEZDGNBVGY3TQOJQ". At t=59 the 8-digit code is
// 94287082, whose last 6 digits are 287082.
const RFC_SECRET = 'GEZDGNBVGY3TQOJQGEZDGNBVGY3TQOJQ';

test('codeAt matches the RFC 6238 SHA1 test vectors', function (int $timestamp, string $code) {
    expect(Totp::codeAt(RFC_SECRET, $timestamp))->toBe($code);
})->with([
    [59, '287082'],
    [1111111109, '081804'],
    [1111111111, '050471'],
    [1234567890, '005924'],
    [2000000000, '279037'],
    [20000000000, '353130'],
]);

test('generateSecret returns a 16-char base32 string', function () {
    $secret = Totp::generateSecret();

    expect($secret)->toHaveLength(16)
        ->and($secret)->toMatch('/^[A-Z2-7]+$/');
});

test('verify accepts the current code and rejects a malformed one', function () {
    $secret = Totp::generateSecret();
    $valid = Totp::codeAt($secret, time());

    expect(Totp::verify($secret, $valid))->toBeTrue()
        ->and(Totp::verify($secret, 'abc'))->toBeFalse();
});

test('verify rejects malformed codes', function (string $code) {
    expect(Totp::verify(RFC_SECRET, $code))->toBeFalse();
})->with(['', '12345', '1234567', '12 3456', '12345a', '-12345']);

test('codes stay constant within a time step and change at the next step', function () {
    expect(Totp::codeAt(RFC_SECRET, 30))->toBe(Totp::codeAt(RFC_SECRET, 59))
        ->and(Totp::codeAt(RFC_SECRET, 60))->not->toBe(Totp::codeAt(RFC_SECRET, 59));
});

test('base32 secrets accept lowercase and padding', function () {
    expect(Totp::codeAt(strtolower(RFC_SECRET) . '====', 59))->toBe('287082');
});

test('provisioningUri embeds the secret and issuer', function () {
    $uri = Totp::provisioningUri('ABC234', 'user@example.com', 'Phare');

    expect($uri)->toStartWith('otpauth://totp/')
        ->and($uri)->toContain('secret=ABC234')
        ->and($uri)->toContain('issuer=Phare');
});

test('provisioningUri safely encodes labels and issuers', function () {
    $uri = Totp::provisioningUri('ABC234', 'user+test@example.com', 'Phare & Co');
    parse_str(parse_url($uri, PHP_URL_QUERY), $query);

    expect(rawurldecode(ltrim(parse_url($uri, PHP_URL_PATH), '/')))->toBe('Phare & Co:user+test@example.com')
        ->and($query)->toBe(['secret' => 'ABC234', 'issuer' => 'Phare & Co', 'period' => '30', 'digits' => '6']);
});

test('generateRecoveryCodes returns the requested count of unique codes', function () {
    $codes = User::generateRecoveryCodes(8);

    expect($codes)->toHaveCount(8)
        ->and(array_unique($codes))->toHaveCount(8);
});
