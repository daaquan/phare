<?php

namespace App\Http\Middleware;

use App\Support\Auth\AuthSession;
use Phalcon\Http\RequestInterface;
use Phalcon\Http\ResponseInterface;
use Phare\Auth\Middleware\Authenticate as Middleware;

/**
 * `auth` alias: also covers API routes, which do not run the web group.
 */
class Authenticate extends Middleware
{
    public function handle(RequestInterface $request, \Closure $next): ResponseInterface
    {
        AuthSession::enforceVersion();

        return parent::handle($request, $next);
    }
}
