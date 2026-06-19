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
