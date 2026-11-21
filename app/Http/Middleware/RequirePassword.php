<?php

namespace App\Http\Middleware;

use Phalcon\Http\RequestInterface;
use Phalcon\Http\ResponseInterface;
use Phare\Contracts\Http\MiddlewareContract;
use Phare\Foundation\Http\Concerns\BeforeMiddleware;

/**
 * Equivalent to Laravel's `password.confirm` middleware. Redirects to the
 * confirmation screen unless the password was re-confirmed recently
 * (`auth.password_confirmed_at`), stashing where to come back to in `url.intended`.
 *
 * fetch()/JSON callers (passkeys, recovery codes) get 423 JSON instead of a
 * redirect they cannot follow; the frontend then visits the confirm page.
 */
class RequirePassword extends MiddlewareContract implements BeforeMiddleware
{
    public const CONFIRM_URL = '/user/confirm-password';

    public function handle(RequestInterface $request, \Closure $next): ResponseInterface
    {
        if (self::recentlyConfirmed()) {
            return $next($request);
        }

        $session = app('session');
        $session->set('url.intended', self::intendedUrl($request));

        $response = app('response');
        if (self::wantsJson($request)) {
            $response->setStatusCode(423, 'Locked');
            $response->setJsonContent([
                'message' => 'Please confirm your password to continue.',
                'redirect' => self::CONFIRM_URL,
            ]);

            return $response;
        }

        return $response->redirect(self::CONFIRM_URL);
    }

    public static function recentlyConfirmed(): bool
    {
        $confirmedAt = (int)app('session')->get('auth.password_confirmed_at', 0);
        $timeout = (int)config('auth.password_timeout', 10800);

        return time() - $confirmedAt <= $timeout;
    }

    /**
     * Where to return after confirming. A GET comes back to itself; a POST /
     * PATCH / DELETE cannot be replayed as a GET (it would 404), so it returns
     * to the page that sent it. Only the path of the Referer is kept, never its
     * host, so this cannot become an open redirect.
     */
    public static function intendedUrl(RequestInterface $request): string
    {
        if (strtoupper((string)$request->getMethod()) === 'GET') {
            return (string)$request->getURI();
        }

        $referer = parse_url((string)$request->getHTTPReferer());
        $path = is_array($referer) ? (string)($referer['path'] ?? '') : '';

        if ($path === '' || $path[0] !== '/' || str_starts_with($path, '//') || $path === self::CONFIRM_URL) {
            return '/dashboard';
        }

        return $path . (isset($referer['query']) ? '?' . $referer['query'] : '');
    }

    private static function wantsJson(RequestInterface $request): bool
    {
        if ((string)$request->getHeader('X-Inertia') !== '') {
            return false;
        }

        return str_contains((string)$request->getHeader('Accept'), 'application/json')
            || str_contains((string)$request->getHeader('Content-Type'), 'application/json');
    }
}
