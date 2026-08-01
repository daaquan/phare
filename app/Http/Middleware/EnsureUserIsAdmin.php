<?php

namespace App\Http\Middleware;

use App\Models\User;
use Phalcon\Http\RequestInterface;
use Phalcon\Http\ResponseInterface;
use Phare\Contracts\Http\MiddlewareContract;
use Phare\Foundation\Http\Concerns\BeforeMiddleware;
use Phare\Support\Facades\Auth;

/**
 * Restricts admin screens (the broadcasting monitor and friends) to admins.
 * Non-admins and guests are redirected to the top page.
 */
class EnsureUserIsAdmin extends MiddlewareContract implements BeforeMiddleware
{
    public function handle(RequestInterface $request, \Closure $next): ResponseInterface
    {
        $user = Auth::user();

        if (!$user instanceof User || !$user->isAdmin()) {
            return app('response')->redirect('/');
        }

        return $next($request);
    }
}
