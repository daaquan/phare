<?php

namespace App\Http;

use Framework\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    protected array $middleware = [
        'before' => [
            //\Framework\Foundation\Http\Middlewares\WhitelistMiddleware::class,
            \Framework\Foundation\Http\Middlewares\NotFoundMiddleware::class,
        ],
        'after' => [
            \App\Http\Middleware\ExampleMiddleware::class,
        ],
    ];
}