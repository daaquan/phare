<?php

use App\Models\User;
use App\Support\Auth\AuthSession;
use App\Support\Auth\CredentialChange;
use App\Support\Concurrency\FileLock;
use App\Support\TwoFactor\Totp;

/*
 * Races around password change, reset tokens and session state. Concurrency is
 * simulated deterministically: a stale model for lost updates, a lock held by
 * the test for "another request is in the critical section".
 */

function raceTwoFactorUser(): User
{
    return createTestUser([
        'two_factor_secret' => Totp::generateSecret(),
        'two_factor_recovery_codes' => json_encode(['ABCDEF1234']),
        'two_factor_confirmed_at' => '2026-01-01 00:00:00',
    ]);
}

function raceSignedIn(): bool
{
    return app('session')->has((string)config('auth.session_id', 'auth'));
}

function resetPost($test, User $user, string $token): void
{
    $test->post('/user/reset-password', [
        'email' => (string)$user->email,
        'token' => $token,
        'password' => 'brand-new-pass',
        'password_confirmation' => 'brand-new-pass',
    ]);
}

beforeEach(function () {
    foreach (['login.id', 'login.at', 'login.version', 'auth.password_confirmed_at'] as $key) {
        app('session')->remove($key);
    }
});

// 1. pending 2FA login vs password change

test('a pending two-factor login cannot finish after the password changed', function () {
    $user = raceTwoFactorUser();
    AuthSession::beginTwoFactor($user); // password verified here

    CredentialChange::setPassword(freshUser((int)$user->id), 'brand-new-pass');

    $this->post('/user/two-factor-challenge', ['recovery_code' => 'ABCDEF1234'])
        ->assertStatus(302)
        ->assertHeader('Location', app('url')->get('/user/login'));

    expect(raceSignedIn())->toBeFalse()
        ->and(app('session')->has('login.id'))->toBeFalse()
        ->and(freshUser((int)$user->id)->recoveryCodes())->toBe(['ABCDEF1234']);
});

test('a pending login without a recorded session version fails closed', function () {
    $user = raceTwoFactorUser();
    app('session')->set('login.id', $user->id);
    app('session')->set('login.at', time());

    $this->post('/user/two-factor-challenge', ['recovery_code' => 'ABCDEF1234'])
        ->assertHeader('Location', app('url')->get('/user/login'));

    expect(raceSignedIn())->toBeFalse();
});

test('a pending login still completes when nothing changed, with the recorded version', function () {
    $user = raceTwoFactorUser();
    AuthSession::beginTwoFactor($user);

    $this->post('/user/two-factor-challenge', ['recovery_code' => 'ABCDEF1234'])
        ->assertHeader('Location', app('url')->get('/dashboard'));

    expect(raceSignedIn())->toBeTrue()
        ->and(app('session')->get(AuthSession::VERSION_KEY))->toBe(0);
});

// 2. reset token verify / consume / revoke

test('a reset token is consumed under the per-email lock and fails closed when the lock is held', function () {
    $user = createTestUser(['email' => 'lock@example.com']);
    $token = app('password.broker')->createToken($user);

    FileLock::run('reset_email_' . sha1('lock@example.com'), function () use ($user, $token) {
        resetPost($this, $user, $token);
    });

    expect(password_verify('secret123', (string)freshUser((int)$user->id)->password))->toBeTrue()
        ->and(app('password.broker')->validateToken('lock@example.com', $token))->toBeTrue();
});

test('a reset token works exactly once', function () {
    $user = createTestUser();
    $token = app('password.broker')->createToken($user);

    resetPost($this, $user, $token);
    expect(freshUser((int)$user->id)->sessionVersion())->toBe(1);

    $this->post('/user/reset-password', [
        'email' => (string)$user->email, 'token' => $token,
        'password' => 'second-attempt-pass', 'password_confirmation' => 'second-attempt-pass',
    ]);

    expect(freshUser((int)$user->id)->sessionVersion())->toBe(1)
        ->and(password_verify('brand-new-pass', (string)freshUser((int)$user->id)->password))->toBeTrue();
});

test('a reset link request does not write a token once the account is gone, and fails closed under the lock', function () {
    $user = createTestUser(['email' => 'issue@example.com']);
    $broker = app('password.broker');

    // Another request is changing/deleting the account: issuing must not write a token.
    FileLock::run('reset_email_' . sha1('issue@example.com'), function () {
        $this->post('/user/forgot-password', ['email' => 'issue@example.com'])->assertStatus(302);
    });
    expect($broker->validateToken('issue@example.com', 'x'))->toBeFalse();
    expect(passwordTokenCount('issue@example.com'))->toBe(0);

    // Account deleted, then the in-flight request runs: the re-read finds nobody.
    $user->delete();
    $this->post('/user/forgot-password', ['email' => 'issue@example.com'])->assertStatus(302);
    expect(passwordTokenCount('issue@example.com'))->toBe(0);
});

function passwordTokenCount(string $email): int
{
    $db = app('db')->getInternalHandler();
    $stmt = $db->prepare('SELECT COUNT(*) FROM password_reset_tokens WHERE email = ?');
    $stmt->execute([$email]);

    return (int)$stmt->fetchColumn();
}

test('changing the email and requesting a link serialise on the old address', function () {
    $user = signIn(createTestUser(['email' => 'old2@example.com']));
    app('session')->set('auth.password_confirmed_at', time());

    FileLock::run('reset_email_' . sha1('old2@example.com'), function () {
        $this->patch('/settings/profile', ['name' => 'Owner', 'email' => 'new2@example.com'])->assertStatus(302);
    });

    // Held lock -> the change is refused, nothing half-applied.
    expect((string)freshUser((int)$user->id)->email)->toBe('old2@example.com');
});

// 3. concurrent password change

test('a stale model cannot reuse a session version already issued', function () {
    $a = createTestUser();
    $stale = freshUser((int)$a->id);

    CredentialChange::setPassword(freshUser((int)$a->id), 'first-new-pass');
    $second = CredentialChange::setPassword($stale, 'second-new-pass');

    expect($second)->toBeInstanceOf(User::class)
        ->and($second->sessionVersion())->toBe(2)
        ->and(freshUser((int)$a->id)->sessionVersion())->toBe(2)
        ->and(password_verify('second-new-pass', (string)freshUser((int)$a->id)->password))->toBeTrue();
});

test('a password change fails closed when the account lock is held', function () {
    $user = createTestUser();

    $result = FileLock::run('password_change_' . (int)$user->id, fn () => CredentialChange::setPassword(freshUser((int)$user->id), 'brand-new-pass'));

    expect($result)->toBeNull()
        ->and(freshUser((int)$user->id)->sessionVersion())->toBe(0);
});

// 4. identity switch

test('signing in as another account drops the previous password confirmation', function () {
    $first = signIn(createTestUser());
    app('session')->set('auth.password_confirmed_at', time());
    $second = createTestUser();

    $this->post('/user/login', ['email' => (string)$second->email, 'password' => 'secret123'])->assertStatus(302);

    expect(app('session')->has('auth.password_confirmed_at'))->toBeFalse()
        ->and(app('session')->get(AuthSession::VERSION_KEY))->toBe(0);
});

test('the email lock key folds case, accents and padding like the column collation', function () {
    expect(CredentialChange::lockKey(' José@Example.com '))->toBe(CredentialChange::lockKey('jose@example.com'));
});
