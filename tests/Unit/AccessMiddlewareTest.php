<?php

use App\Http\Middleware\EnsureEmailIsVerified;
use App\Http\Middleware\EnsureUserIsAdmin;
use App\Http\Middleware\RedirectIfAuthenticated;
use App\Http\Middleware\RequirePassword;
use App\Models\User;
use Phalcon\Http\Request;
use Phalcon\Http\RequestInterface;
use Phalcon\Http\Response;
use Phare\Support\Facades\Auth;

beforeEach(function () {
    $this->originalAuth = Auth::getFacadeRoot();
});

afterEach(function () {
    Auth::swap($this->originalAuth);
    Auth::clearResolvedInstance();
});

function middlewareAuth(?User $user): void
{
    Auth::swap(new class($user)
    {
        public function __construct(private ?User $currentUser) {}

        public function user(): ?User
        {
            return $this->currentUser;
        }

        public function check(): bool
        {
            return $this->currentUser !== null;
        }
    });
}

test('admin middleware allows only administrators', function (?bool $admin) {
    $user = $admin === null ? null : new User();
    if ($user !== null) {
        $user->writeAttribute('is_admin', $admin);
    }
    middlewareAuth($user);
    $request = new Request();
    $allowed = new Response();
    $called = false;

    $response = (new EnsureUserIsAdmin())->handle($request, function ($forwarded) use ($request, $allowed, &$called) {
        expect($forwarded)->toBe($request);
        $called = true;

        return $allowed;
    });

    expect($called)->toBe($admin === true);
    if ($admin === true) {
        expect($response)->toBe($allowed);
    } else {
        expect($response->getStatusCode())->toBe(302)
            ->and($response->getHeaders()->get('Location'))->toBe(app('url')->get('/'));
    }
})->with(['guest' => [null], 'ordinary user' => [false], 'administrator' => [true]]);

test('verification middleware blocks unverified users', function (?bool $verified) {
    $user = $verified === null ? null : new User();
    if ($user !== null) {
        $user->email_verified_at = $verified ? '2026-01-01 00:00:00' : null;
    }
    middlewareAuth($user);
    $allowed = new Response();
    $called = false;

    $response = (new EnsureEmailIsVerified())->handle(new Request(), function () use ($allowed, &$called) {
        $called = true;

        return $allowed;
    });

    expect($called)->toBe($verified !== false);
    if ($verified === false) {
        expect($response->getStatusCode())->toBe(302)
            ->and($response->getHeaders()->get('Location'))->toBe(app('url')->get('/user/verify-email'));
    } else {
        expect($response)->toBe($allowed);
    }
})->with(['guest handled by auth middleware' => [null], 'unverified' => [false], 'verified' => [true]]);

test('guest middleware redirects authenticated users', function (bool $authenticated) {
    middlewareAuth($authenticated ? new User() : null);
    $allowed = new Response();
    $called = false;

    $response = (new RedirectIfAuthenticated())->handle(new Request(), function () use ($allowed, &$called) {
        $called = true;

        return $allowed;
    });

    expect($called)->toBe(!$authenticated);
    if ($authenticated) {
        expect($response->getStatusCode())->toBe(302)
            ->and($response->getHeaders()->get('Location'))->toBe(app('url')->get('/dashboard'));
    } else {
        expect($response)->toBe($allowed);
    }
})->with([false, true]);

test('password confirmation redirects missing or expired confirmations and preserves the intended URL', function (bool $expired) {
    $session = app('session');
    $session->remove('auth.password_confirmed_at');
    $session->remove('url.intended');
    if ($expired) {
        $session->set('auth.password_confirmed_at', time() - (int)config('auth.password_timeout', 10800) - 60);
    }
    $request = $this->createMock(RequestInterface::class);
    $request->method('getURI')->willReturn('/settings/security?tab=passkeys');

    try {
        $response = (new RequirePassword())->handle($request, function () {
            $this->fail('An expired confirmation must stop dispatch.');
        });

        expect($response->getStatusCode())->toBe(302)
            ->and($response->getHeaders()->get('Location'))->toBe(app('url')->get('/user/confirm-password'))
            ->and($session->get('url.intended'))->toBe('/settings/security?tab=passkeys');
    } finally {
        $session->remove('auth.password_confirmed_at');
        $session->remove('url.intended');
    }
})->with(['missing' => [false], 'expired' => [true]]);

test('recent password confirmation permits dispatch without replacing the intended URL', function () {
    $session = app('session');
    $session->set('auth.password_confirmed_at', time() - 60);
    $session->set('url.intended', '/original');
    $allowed = new Response();
    $request = new Request();

    try {
        $response = (new RequirePassword())->handle($request, function ($forwarded) use ($request, $allowed) {
            expect($forwarded)->toBe($request);

            return $allowed;
        });

        expect($response)->toBe($allowed)
            ->and($session->get('url.intended'))->toBe('/original');
    } finally {
        $session->remove('auth.password_confirmed_at');
        $session->remove('url.intended');
    }
});
