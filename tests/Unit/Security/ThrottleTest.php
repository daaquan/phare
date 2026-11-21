<?php

use App\Http\Middleware\ThrottlePasswordChecks;
use App\Http\Middleware\ThrottleRequests;
use App\Http\Middleware\ThrottleTwoFactor;
use App\Support\TwoFactor\Totp;
use App\Support\TwoFactor\UsedTotpSteps;
use Phalcon\Http\RequestInterface;
use Phalcon\Http\Response;

/*
 * Behind cloudflared every request reaches PHP from 127.0.0.1. The limiter
 * must count the real client (CF-Connecting-IP) so one visitor cannot exhaust
 * a bucket shared by everybody, and must actually stop the request.
 */

beforeEach(function () {
    $this->savedServer = $_SERVER;
    app('session')->remove('login.id');
    app('cache')->flush();
});

afterEach(function () {
    $_SERVER = $this->savedServer;
    app('session')->remove('login.id');
});

function throttleRequest(string $uri): RequestInterface
{
    $request = test()->createMock(RequestInterface::class);
    $request->method('getURI')->willReturn($uri);

    return $request;
}

/** Run the middleware as a guest coming through cloudflared from $clientIp. */
function throttleAs(ThrottleRequests $middleware, string $clientIp, string $uri = '/user/login'): array
{
    $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
    $_SERVER['HTTP_CF_CONNECTING_IP'] = $clientIp;
    $passed = false;
    $response = $middleware->handle(throttleRequest($uri), function () use (&$passed) {
        $passed = true;

        return new Response();
    });

    return [$passed, $response];
}

test('guests behind the proxy are counted per client IP, not as 127.0.0.1', function () {
    $middleware = new ThrottlePasswordChecks(app());

    for ($i = 0; $i < 5; $i++) {
        expect(throttleAs($middleware, '203.0.113.10')[0])->toBeTrue();
    }

    [$passed, $response] = throttleAs($middleware, '203.0.113.10');
    expect($passed)->toBeFalse()
        ->and($response->getStatusCode())->toBe(429)
        ->and((int)$response->getHeaders()->get('Retry-After'))->toBeGreaterThan(0);

    // Another visitor, same loopback REMOTE_ADDR, still gets through.
    expect(throttleAs($middleware, '198.51.100.20')[0])->toBeTrue();
});

test('the throttle key carries the client IP', function () {
    $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
    $_SERVER['HTTP_CF_CONNECTING_IP'] = '203.0.113.10';
    $middleware = new ThrottleRequests(app());
    $signature = (fn (RequestInterface $request) => $this->signature($request))->call($middleware, throttleRequest('/user/login?x=1'));

    expect($signature)->toBe('/user/login|ip:203.0.113.10');
});

test('buckets are per route path', function () {
    $middleware = new ThrottlePasswordChecks(app());
    for ($i = 0; $i < 5; $i++) {
        throttleAs($middleware, '203.0.113.10', '/user/login');
    }

    expect(throttleAs($middleware, '203.0.113.10', '/user/login')[0])->toBeFalse()
        ->and(throttleAs($middleware, '203.0.113.10', '/user/confirm-password')[0])->toBeTrue();
});

test('the default throttle allows 60 requests a minute', function () {
    $middleware = new ThrottleRequests(app());
    for ($i = 0; $i < 60; $i++) {
        expect(throttleAs($middleware, '203.0.113.30')[0])->toBeTrue();
    }

    expect(throttleAs($middleware, '203.0.113.30')[0])->toBeFalse();
});

test('two-factor attempts are counted per pending login across IPs', function () {
    $middleware = new ThrottleTwoFactor(app());
    app('session')->set('login.id', 42);

    foreach (['203.0.113.1', '203.0.113.2', '203.0.113.3', '203.0.113.4', '203.0.113.5'] as $ip) {
        expect(throttleAs($middleware, $ip, '/user/two-factor-challenge')[0])->toBeTrue();
    }

    expect(throttleAs($middleware, '203.0.113.6', '/user/two-factor-challenge')[0])->toBeFalse();

    // A different pending login is unaffected.
    app('session')->set('login.id', 43);
    expect(throttleAs($middleware, '203.0.113.6', '/user/two-factor-challenge')[0])->toBeTrue();
});

test('a TOTP time step is accepted once per user', function () {
    $secret = Totp::generateSecret();
    $now = 1800000000;
    $step = Totp::verifyStep($secret, Totp::codeAt($secret, $now), 1, $now);

    expect($step)->toBe(intdiv($now, 30))
        ->and(UsedTotpSteps::claim(7, $step))->toBeTrue()
        ->and(UsedTotpSteps::claim(7, $step))->toBeFalse()
        ->and(UsedTotpSteps::claim(7, $step - 1))->toBeFalse()
        ->and(UsedTotpSteps::claim(8, $step))->toBeTrue()
        ->and(UsedTotpSteps::claim(7, $step + 1))->toBeTrue();
});

test('verifyStep reports the matched step within the window and null outside it', function () {
    $secret = Totp::generateSecret();
    $now = 1800000000;

    expect(Totp::verifyStep($secret, Totp::codeAt($secret, $now - 30), 1, $now))->toBe(intdiv($now, 30) - 1)
        ->and(Totp::verifyStep($secret, Totp::codeAt($secret, $now - 90), 1, $now))->toBeNull()
        ->and(Totp::verifyStep($secret, 'abcdef', 1, $now))->toBeNull();
});
