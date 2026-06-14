<?php

use App\Models\User;

/*
 * The verification hash is Laravel-compatible: sha1 of the email. The full
 * verify route additionally requires auth; this exercises the hash itself.
 */

test('the verification hash matches sha1 of the email', function () {
    $user = new User();
    $user->email = 'verify@example.com';

    expect($user->verificationHash())->toBe(sha1('verify@example.com'))
        ->and(hash_equals($user->verificationHash(), sha1('verify@example.com')))->toBeTrue()
        ->and(hash_equals($user->verificationHash(), sha1('other@example.com')))->toBeFalse();
});
