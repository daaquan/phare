<?php

namespace App\Http\Middleware;

use Framework\Contracts\Http\MiddlewareContract;
use Phalcon\Http\Request;
use Phalcon\Http\Response;

class ExampleMiddleware extends MiddlewareContract
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Response $response)
    {
        if ($request->getHeader('X-DEBUG-MODE')) {
            $response->setHeader('REQUEST-START-TIME', APP_START);
        }
        return true;
    }
}