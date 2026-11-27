<?php

use App\Http\Middleware\VerifyCsrfToken;
use Phalcon\Http\RequestInterface;
use Phalcon\Http\Response;
use Phare\Security\Csrf;

/*
 * The framework's VerifyCsrfToken implements only the bare Middleware
 * interface and was never invoked. App\Http\Middleware\VerifyCsrfToken runs
 * through the events bridge and must refuse web writes without the token.
 */

beforeEach(function () {
    unset($_SERVER['HTTP_X_CSRF_TOKEN']); // drop the default test token from Pest.php
});

test('a web POST without a CSRF token is refused with 419', function () {
    $this->post('/user/forgot-password', ['email' => 'nobody@example.com'])->assertStatus(419);
});

test('a web POST with a wrong CSRF token is refused with 419', function () {
    $this->post('/user/forgot-password', ['email' => 'nobody@example.com'], ['X-CSRF-TOKEN' => 'not-the-token'])
        ->assertStatus(419);
});

test('a refused write never reaches the controller', function () {
    $user = signIn(createTestUser());

    $this->put('/settings/password', [
        'current_password' => 'secret123',
        'password' => 'brand-new-pass',
        'password_confirmation' => 'brand-new-pass',
    ])->assertStatus(419);

    expect(password_verify('secret123', (string)freshUser((int)$user->id)->password))->toBeTrue();
});

test('the token is accepted in X-CSRF-TOKEN, X-XSRF-TOKEN and _token', function (string $where) {
    $token = app(Csrf::class)->getToken();
    $headers = $where === 'header' ? ['X-CSRF-TOKEN' => $token] : ($where === 'xsrf' ? ['X-XSRF-TOKEN' => $token] : []);
    $data = ['email' => 'nobody@example.com'] + ($where === 'body' ? ['_token' => $token] : []);

    $response = $this->post('/user/forgot-password', $data, $headers);
    expect((fn () => $this->getStatusCode())->call($response))->not->toBe(419);
})->with(['header' => ['header'], 'xsrf header' => ['xsrf'], 'body' => ['body']]);

test('safe methods need no token', function () {
    // GET never reaches the 419 branch; asserting the middleware directly avoids rendering the Vite page.
    $middleware = new VerifyCsrfToken(app());
    $request = test()->createMock(RequestInterface::class);
    $request->method('getMethod')->willReturn('GET');
    $called = false;
    $middleware->handle($request, function () use (&$called) {
        $called = true;

        return new Response();
    });
    expect($called)->toBeTrue();
});
