<?php

namespace App\Http\Middleware;

use Phalcon\Http\RequestInterface;
use Phalcon\Http\ResponseInterface;
use Phare\Contracts\Http\MiddlewareContract;
use Phare\Foundation\Http\Concerns\BeforeMiddleware;
use Phare\Support\Facades\Auth;

/**
 * Sends authenticated-but-unverified users to the email-verification prompt
 * before they can reach protected routes.
 */
class EnsureEmailIsVerified extends MiddlewareContract implements BeforeMiddleware
{
    public function handle(RequestInterface $request, \Closure $next): ResponseInterface
    {
        $user = Auth::user();

        if ($user !== null && method_exists($user, 'hasVerifiedEmail') && !$user->hasVerifiedEmail()) {
            return app('response')->redirect('/user/verify-email');
        }

        return $next($request);
    }
}
