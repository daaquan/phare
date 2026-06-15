<?php

namespace App\Http\Middleware;

use Phalcon\Http\RequestInterface;
use Phalcon\Http\ResponseInterface;
use Phare\Contracts\Http\MiddlewareContract;
use Phare\Foundation\Http\Concerns\BeforeMiddleware;

/**
 * Laravel の `password.confirm` ミドルウェア相当。直近で
 * パスワード再確認 (`auth.password_confirmed_at`) が行われていなければ
 * 確認画面へリダイレクトし、本来のURLを `url.intended` に退避する。
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
