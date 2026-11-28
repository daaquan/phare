<?php

namespace App\Http\Middleware;

use App\Support\Auth\AuthSession;
use Phalcon\Http\RequestInterface;
use Phalcon\Http\ResponseInterface;
use Phare\Contracts\Http\MiddlewareContract;
use Phare\Foundation\Http\Concerns\BeforeMiddleware;

/**
 * Signs out sessions issued before the account's last password change. The
 * request then continues as a guest, so `auth` redirects to login.
 */
class ValidateSessionVersion extends MiddlewareContract implements BeforeMiddleware
{
    public function handle(RequestInterface $request, \Closure $next): ResponseInterface
    {
        AuthSession::enforceVersion();

        return $next($request);
    }
}
