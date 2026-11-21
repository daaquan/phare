<?php

use App\Http\Middleware\ThrottlePasswordChecks;
use App\Http\Middleware\ThrottleRequests;
use App\Http\Middleware\ThrottleTwoFactor;
use Phare\Contracts\Http\Kernel;

/*
 * The throttle aliases resolve to middleware that the Phalcon events bridge
 * actually runs (the framework's ThrottleRequests never ran), and every
 * password-checking endpoint carries one.
 */

test('a throttled route reports its limit, proving the middleware ran', function (string $method, string $uri, int $limit) {
    $response = $this->call($method, $uri, ['email' => 'nobody@example.com', 'password' => 'x']);

    $response->assertHeader('X-RateLimit-Limit', (string)$limit);
})->with([
    'web login' => ['POST', '/user/login', 5],
    'API login' => ['POST', '/api/auth/login', 5],
    'password reset' => ['POST', '/user/reset-password', 5],
    'forgot password' => ['POST', '/user/forgot-password', 60],
    'two-factor challenge' => ['POST', '/user/two-factor-challenge', 5],
]);

test('password checks for signed-in users are throttled', function (string $method, string $uri) {
    signIn(createTestUser());

    $this->call($method, $uri, ['password' => 'wrong', 'current_password' => 'wrong'])
        ->assertHeader('X-RateLimit-Limit', '5');
})->with([
    'change password' => ['PUT', '/settings/password'],
    'confirm password' => ['POST', '/user/confirm-password'],
    'delete account' => ['DELETE', '/settings/profile'],
]);

test('the kernel maps the throttle aliases to the app middleware', function () {
    $aliases = (fn () => $this->routeMiddleware)->call(app(Kernel::class));

    expect($aliases['throttle'])->toBe(ThrottleRequests::class)
        ->and($aliases['throttle.password'])->toBe(ThrottlePasswordChecks::class)
        ->and($aliases['throttle.two-factor'])->toBe(ThrottleTwoFactor::class);
});

/*
 * End to end: needs a cache that outlives the per-request application rebuild.
 * Run with: CACHE_DRIVER=file ./vendor/bin/pest --filter="sixth attempt"
 */
test('the sixth login attempt in a minute is refused with 429', function (string $uri) {
    app('cache')->flush();
    $_SERVER['HTTP_CF_CONNECTING_IP'] = '203.0.113.77';

    try {
        for ($i = 0; $i < 5; $i++) {
            $this->post($uri, ['email' => 'nobody@example.com', 'password' => 'x'], ['CF-Connecting-IP' => '203.0.113.77']);
        }

        $this->post($uri, ['email' => 'nobody@example.com', 'password' => 'x'], ['CF-Connecting-IP' => '203.0.113.77'])
            ->assertStatus(429)
            ->assertSee('Too many attempts');

        // A different visitor behind the same proxy is not locked out.
        $this->post($uri, ['email' => 'nobody@example.com', 'password' => 'x'], ['CF-Connecting-IP' => '198.51.100.77'])
            ->assertHeader('X-RateLimit-Remaining', '4');
    } finally {
        app('cache')->flush();
    }
})->with(['web login' => ['/user/login'], 'API login' => ['/api/auth/login']])
    ->skip(fn () => config('cache.default') !== 'file', 'needs CACHE_DRIVER=file (array cache resets per request)');
