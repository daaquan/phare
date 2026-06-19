<?php

use App\Support\WebAuthn\WebAuthnService;

/**
 * パスキーの暗号検証は web-auth/webauthn-lib（独自のテスト済み）が担うため、
 * ここはアプリ側の結線（オプション生成・base64url）だけを検証する。
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
