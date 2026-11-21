<?php

use App\Models\User;

/*
 * Verification links are HMAC-signed (id|email|expires, keyed by APP_KEY),
 * expire after User::VERIFICATION_TTL, and only verify the signed-in user.
 */

function signedLinkParts(User $user, int $now): array
{
    // /user/verify-email/{id}/{expires}/{hash}
    return array_slice(explode('/', $user->verificationPath($now)), 3);
}

test('the verification hash is not sha1 of the email and depends on every signed field', function () {
    $user = new User();
    $user->id = 7;
    $user->email = 'verify@example.com';
    $hash = $user->verificationHash(2000000000);

    $other = new User();
    $other->id = 8;
    $other->email = 'verify@example.com';

    expect($hash)->not->toBe(sha1('verify@example.com'))
        ->and($hash)->toMatch('/^[0-9a-f]{64}$/')
        ->and($user->verificationHash(2000000001))->not->toBe($hash)
        ->and($other->verificationHash(2000000000))->not->toBe($hash);

    $user->email = 'changed@example.com';
    expect($user->verificationHash(2000000000))->not->toBe($hash);
});

test('signature checks reject forged, expired and mismatched links', function () {
    $user = new User();
    $user->id = 7;
    $user->email = 'verify@example.com';
    $now = 1800000000;
    [$id, $expires, $hash] = signedLinkParts($user, $now);

    expect($user->hasValidVerificationSignature($id, $expires, $hash, $now))->toBeTrue()
        ->and($user->hasValidVerificationSignature($id, $expires, sha1('verify@example.com'), $now))->toBeFalse()
        ->and($user->hasValidVerificationSignature($id, $expires, $hash, $now + User::VERIFICATION_TTL + 1))->toBeFalse()
        ->and($user->hasValidVerificationSignature('8', $expires, $hash, $now))->toBeFalse()
        ->and($user->hasValidVerificationSignature($id, (string)((int)$expires + 60), $hash, $now))->toBeFalse()
        ->and($user->hasValidVerificationSignature('7abc', $expires, $hash, $now))->toBeFalse();
});

test('verification links cannot be signed without an APP_KEY', function () {
    $config = app('config');
    $original = $config->path('app.key');
    $config->app->key = '';

    try {
        $user = new User();
        $user->id = 1;
        $user->email = 'a@example.com';

        expect(fn () => $user->verificationHash(time()))->toThrow(RuntimeException::class);
    } finally {
        $config->app->key = $original;
    }
});

dataset('rejected verification links', [
    'forged sha1 hash (old scheme)' => [fn (User $user) => '/user/verify-email/' . $user->id . '/' . (time() + 600) . '/' . sha1((string)$user->email)],
    'expired signature' => [fn (User $user) => $user->verificationPath(time() - User::VERIFICATION_TTL - 60)],
    'another user id' => [fn (User $user) => createTestUser(['email_verified_at' => null])->verificationPath()],
    'tampered expiry' => [function (User $user) {
        [$id, $expires, $hash] = signedLinkParts($user, time());

        return "/user/verify-email/{$id}/" . ((int)$expires + 86400) . "/{$hash}";
    }],
]);

test('the verify route rejects invalid links and leaves the email unverified', function (Closure $link) {
    $user = signIn(createTestUser(['email_verified_at' => null]));

    $this->get($link($user))
        ->assertStatus(302)
        ->assertHeader('Location', app('url')->get('/user/verify-email'));

    expect(freshUser((int)$user->id)->hasVerifiedEmail())->toBeFalse();
})->with('rejected verification links');

test('a valid signed link verifies the signed-in user', function () {
    $user = signIn(createTestUser(['email_verified_at' => null]));

    $this->get($user->verificationPath())
        ->assertStatus(302)
        ->assertHeader('Location', app('url')->get('/dashboard?verified=1'));

    expect(freshUser((int)$user->id)->hasVerifiedEmail())->toBeTrue();
});

test('the verify route requires a signed-in user', function () {
    $user = createTestUser(['email_verified_at' => null]);

    $this->get($user->verificationPath())
        ->assertStatus(302)
        ->assertHeader('Location', app('url')->get(route('login')));

    expect(freshUser((int)$user->id)->hasVerifiedEmail())->toBeFalse();
});
