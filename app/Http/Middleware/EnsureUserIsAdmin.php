<?php

namespace App\Http\Middleware;

use Phalcon\Http\RequestInterface;
use Phalcon\Http\ResponseInterface;
use Phare\Contracts\Http\MiddlewareContract;
use Phare\Foundation\Http\Concerns\BeforeMiddleware;
use Phare\Support\Facades\Auth;

/**
 * 管理画面 (broadcasting monitor 等) を管理者のみに制限する。
 * 非管理者・未認証はトップへリダイレクト。
 */
class EnsureUserIsAdmin extends MiddlewareContract implements BeforeMiddleware
{
    public function handle(RequestInterface $request, \Closure $next): ResponseInterface
    {
        $user = Auth::user();

        if ($user === null || !method_exists($user, 'isAdmin') || !$user->isAdmin()) {
            return app('response')->redirect('/');
        }

        return $next($request);
    }
}
