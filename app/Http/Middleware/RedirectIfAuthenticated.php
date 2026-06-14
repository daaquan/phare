<?php

namespace App\Http\Middleware;

use Phalcon\Http\RequestInterface;
use Phalcon\Http\ResponseInterface;
use Phare\Contracts\Http\MiddlewareContract;
use Phare\Foundation\Http\Concerns\BeforeMiddleware;
use Phare\Support\Facades\Auth;

/**
 * Keeps authenticated users off the guest-only auth pages (login, register,
 * password reset) by bouncing them to the dashboard.
 */
class RedirectIfAuthenticated extends MiddlewareContract implements BeforeMiddleware
{
    public function handle(RequestInterface $request, \Closure $next): ResponseInterface
    {
        if (Auth::check()) {
            return app('response')->redirect('/dashboard');
        }

        return $next($request);
    }
}
