<?php

use App\Support\WebAuthn\WebAuthnService;

/**
 * The passkey cryptography is verified by web-auth/webauthn-lib, which has its own
 * tests, so this only covers the app-side wiring: option building and base64url.
 */
it('builds WebAuthn request options as valid JSON carrying a challenge', function () {
    $json = (new WebAuthnService())->requestOptions();
    $options = json_decode($json, true);

    expect($options)->toBeArray()
        ->and($options)->toHaveKey('challenge')
        ->and($options['challenge'])->toBeString()->not->toBeEmpty()
        ->and($options)->toHaveKey('rpId');
});

it('round-trips base64url encoding without padding or url-unsafe chars', function () {
    $bytes = random_bytes(40);
    $encoded = WebAuthnService::base64urlEncode($bytes);

    expect($encoded)->not->toContain('+')
        ->and($encoded)->not->toContain('/')
        ->and($encoded)->not->toContain('=')
        ->and(WebAuthnService::base64urlDecode($encoded))->toBe($bytes);
});

it('encodes and decodes known base64url vectors', function (string $bytes, string $encoded) {
    expect(WebAuthnService::base64urlEncode($bytes))->toBe($encoded)
        ->and(WebAuthnService::base64urlDecode($encoded))->toBe($bytes);
})->with([
    'empty' => ['', ''],
    'one byte' => ['f', 'Zg'],
    'two bytes' => ['fo', 'Zm8'],
    'three bytes' => ['foo', 'Zm9v'],
    'binary URL characters' => ["\xfb\xff", '-_8'],
]);

it('issues independent 32-byte challenges for discoverable login', function () {
    $service = new WebAuthnService();
    $first = json_decode($service->requestOptions(), true, flags: JSON_THROW_ON_ERROR);
    $second = json_decode($service->requestOptions(), true, flags: JSON_THROW_ON_ERROR);
    $host = parse_url((string)(env('APP_URL') ?: 'http://localhost:8000'), PHP_URL_HOST) ?: 'localhost';

    expect(strlen(WebAuthnService::base64urlDecode($first['challenge'])))->toBe(32)
        ->and($first['challenge'])->not->toBe($second['challenge'])
        ->and($first['rpId'])->toBe($host)
        ->and($first['allowCredentials'] ?? [])->toBe([])
        ->and($first['userVerification'])->toBe('preferred');
});
