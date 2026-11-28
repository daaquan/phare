<?php

/*
 * password_reset_tokens is keyed by email, so a token must not outlive the
 * email address or account it was issued for.
 */

test('changing the email revokes the reset token on the old address', function () {
    $user = signIn(createTestUser(['email' => 'old@example.com']));
    $broker = app('password.broker');
    $token = $broker->createToken($user);
    app('session')->set('auth.password_confirmed_at', time());

    $this->patch('/settings/profile', ['name' => 'Owner', 'email' => 'new@example.com'])
        ->assertStatus(302);

    expect((string)freshUser((int)$user->id)->email)->toBe('new@example.com')
        ->and($broker->validateToken('old@example.com', $token))->toBeFalse();
});

test('a refused email change keeps the reset token', function () {
    createTestUser(['email' => 'taken@example.com']);
    $user = signIn(createTestUser(['email' => 'mine@example.com']));
    $broker = app('password.broker');
    $token = $broker->createToken($user);
    app('session')->set('auth.password_confirmed_at', time());

    $this->patch('/settings/profile', ['name' => 'Owner', 'email' => 'taken@example.com']);

    expect($broker->validateToken('mine@example.com', $token))->toBeTrue();
});

test('deleting the account revokes its reset token', function () {
    $user = signIn(createTestUser(['email' => 'gone@example.com']));
    $broker = app('password.broker');
    $token = $broker->createToken($user);

    $this->delete('/settings/profile', ['password' => 'secret123'])->assertStatus(302);

    expect($broker->validateToken('gone@example.com', $token))->toBeFalse();
});
