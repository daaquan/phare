<?php

namespace App\Http\Middleware;

use Phox\Contracts\Http\MiddlewareContract;
use Phalcon\Http\RequestInterface;
use Phalcon\Http\ResponseInterface;

class ExampleMiddleware extends MiddlewareContract
{
    /**
     * Handle an incoming request.
     */
    public function handle(RequestInterface $request, ResponseInterface $response)
    {
        if ($request->getHeader('X-DEBUG-MODE')) {
            $response->setHeader('REQUEST-START-TIME', APP_START);
        }
        return true;
    }
}