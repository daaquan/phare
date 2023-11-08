<?php

namespace App\Http\Middleware;

use Phalcon\Events\Event;
use Phalcon\Http\RequestInterface;
use Phalcon\Http\ResponseInterface;
use Phalcon\Mvc\Micro;
use Phox\Contracts\Http\MiddlewareContract;
use Phox\Foundation\Http\Concerns\BeforeMiddleware;

class ExampleMiddleware extends MiddlewareContract implements BeforeMiddleware
{
    public function before(Event $event, Micro $app): bool
    {
        return true;
    }

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
