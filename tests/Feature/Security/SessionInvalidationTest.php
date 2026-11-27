<?php

use App\Support\Auth\AuthSession;
use Phare\Support\Facades\Auth;

/*
 * Changing the password signs out every OTHER session of the account: each
 * session is stamped with users.session_version at login, the change bumps
 * it, and a stale stamp is signed out on the next request.
 */

function sessionIsSignedIn(): bool
{
    return app('session')->has((string)config('auth.session_id', 'auth'));
}

function changePassword($test): void
{
    $test->put('/settings/password', [
        'current_password' => 'secret123',
        'password' => 'brand-new-pass',
        'password_confirmation' => 'brand-new-pass',
    ])->assertStatus(302);
}

test('after a password change another session must sign in again, this one stays', function () {
    $user = signIn(createTestUser());
    $otherSessionStamp = app('session')->get(AuthSession::VERSION_KEY); // what the other browser holds

    changePassword($this);

    expect(freshUser((int)$user->id)->sessionVersion())->toBe(1)
        ->and(app('session')->get(AuthSession::VERSION_KEY))->toBe(1);
    $this->put('/settings/password', ['current_password' => 'x']); // an authed route that does not render a page
    expect(sessionIsSignedIn())->toBeTrue(); // current session unaffected

    // The other browser's next request carries the old stamp.
    app('session')->set(AuthSession::VERSION_KEY, $otherSessionStamp);
    $this->put('/settings/password', ['current_password' => 'x'])
        ->assertStatus(302)
        ->assertHeader('Location', app('url')->get('/user/login'));
    expect(sessionIsSignedIn())->toBeFalse();
});

test('a session with no stamp is signed out', function () {
    $user = createTestUser();
    Auth::login($user); // not through AuthSession::login()
    app('session')->remove(AuthSession::VERSION_KEY);

    $this->put('/settings/password', ['current_password' => 'x'])->assertStatus(302);
    expect(sessionIsSignedIn())->toBeFalse();
});

test('a wrong current password leaves other sessions valid', function () {
    $user = signIn(createTestUser());

    $this->put('/settings/password', [
        'current_password' => 'wrong',
        'password' => 'brand-new-pass',
        'password_confirmation' => 'brand-new-pass',
    ])->assertStatus(302);

    expect(freshUser((int)$user->id)->sessionVersion())->toBe(0)
        ->and(sessionIsSignedIn())->toBeTrue();
});
