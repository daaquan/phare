<?php

namespace App\Http;

use Framework\Foundation\Http\Kernel as HttpKernel;
use Phalcon\Http\RequestInterface;
use Phalcon\Http\ResponseInterface;

class Kernel extends HttpKernel
{
    protected array $middleware = [
        \App\Http\Middleware\ExampleMiddleware::class,
    ];

    protected array $bootstrappers = [
        \Framework\Foundation\Bootstrap\LoadEnvironmentVariables::class,
        \Framework\Foundation\Bootstrap\LoadConfiguration::class,
        \Framework\Foundation\Bootstrap\RegisterFacades::class,
        \Framework\Foundation\Bootstrap\RegisterProviders::class,
    ];

    public function handle(RequestInterface $request): ResponseInterface
    {
        $response = $this->app->handle($request->getURI());
        return $response ?: $this->app['response'];
    }
}