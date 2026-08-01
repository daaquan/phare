<?php

namespace App\Http\Middleware;

use Phalcon\Http\RequestInterface;
use Phalcon\Http\ResponseInterface;
use Phare\Contracts\Http\MiddlewareContract;
use Phare\Foundation\Http\Concerns\BeforeMiddleware;

/**
 * Equivalent to Laravel's `password.confirm` middleware. Redirects to the
 * confirmation screen unless the password was re-confirmed recently
 * (`auth.password_confirmed_at`), stashing the original URL in `url.intended`.
 */
class RequirePassword extends MiddlewareContract implements BeforeMiddleware
{
    public function handle(RequestInterface $request, \Closure $next): ResponseInterface
    {
        $session = app('session');

        $confirmedAt = (int)$session->get('auth.password_confirmed_at', 0);
        $timeout = (int)config('auth.password_timeout', 10800);

        if (time() - $confirmedAt > $timeout) {
            $session->set('url.intended', $request->getURI());

            return app('response')->redirect('/user/confirm-password');
        }

        return $next($request);
    }
}
