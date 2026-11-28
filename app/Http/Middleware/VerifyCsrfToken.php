<?php

namespace App\Http\Middleware;

use Phalcon\Http\RequestInterface;
use Phalcon\Http\ResponseInterface;
use Phare\Contracts\Foundation\Application;
use Phare\Contracts\Http\MiddlewareContract;
use Phare\Foundation\Http\Concerns\BeforeMiddleware;
use Phare\Security\Csrf;

/**
 * Rejects web write requests (anything but GET/HEAD/OPTIONS) that do not carry
 * the session CSRF token, in `_token`, `X-CSRF-TOKEN` or `X-XSRF-TOKEN`.
 *
 * Replaces Phare\Middleware\VerifyCsrfToken, which implements only the bare
 * Middleware interface: the Phalcon events bridge never called it, so CSRF was
 * not enforced. This one is a BeforeMiddleware like the other web middleware;
 * answering 419 without calling $next halts dispatch (MiddlewareContract).
 */
class VerifyCsrfToken extends MiddlewareContract implements BeforeMiddleware
{
    /**
     * URIs excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected array $except = [
        //
    ];

    public function __construct(protected Application $app) {}

    public function handle(RequestInterface $request, \Closure $next): ResponseInterface
    {
        if ($this->shouldSkip($request) || $this->tokensMatch($request)) {
            return $next($request);
        }

        $response = $this->app['response'];
        $response->setStatusCode(419, 'Page Expired');
        $response->setJsonContent(['message' => 'CSRF token mismatch. Reload the page and try again.']);

        return $response;
    }

    protected function shouldSkip(RequestInterface $request): bool
    {
        if (in_array(strtoupper($request->getMethod()), ['GET', 'HEAD', 'OPTIONS'], true)) {
            return true;
        }

        $path = trim((string)parse_url((string)$request->getURI(), PHP_URL_PATH), '/');
        foreach ($this->except as $pattern) {
            $regex = '#^' . str_replace('\*', '.*', preg_quote(trim($pattern, '/'), '#')) . '$#';
            if (preg_match($regex, $path) === 1) {
                return true;
            }
        }

        return false;
    }

    protected function tokensMatch(RequestInterface $request): bool
    {
        $token = $request->get('_token')
            ?: $request->getHeader('X-CSRF-TOKEN')
            ?: $request->getHeader('X-XSRF-TOKEN');

        $token = is_scalar($token) ? (string)$token : '';

        return $token !== '' && $this->app->make(Csrf::class)->verifyToken($token);
    }
}
