<?php

namespace App\Http\Middleware;

use Phalcon\Http\RequestInterface;
use Phalcon\Http\ResponseInterface;
use Phox\Contracts\Http\MiddlewareContract;
use Phox\Foundation\Http\Concerns\BeforeMiddleware;

class ExampleMiddleware extends MiddlewareContract implements BeforeMiddleware
{
    public function handle(RequestInterface $request, ResponseInterface $response)
    {
        if ($request->getHeader('X-DEBUG-MODE')) {
            $response->setHeader('REQUEST-START-TIME', APP_START);
        }

        return true;
    }
}
