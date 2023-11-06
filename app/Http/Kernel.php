<?php

namespace App\Http;

use Phox\Foundation\Http\Kernel as HttpKernel;
use Phalcon\Http\RequestInterface;
use Phalcon\Http\ResponseInterface;

class Kernel extends HttpKernel
{
    protected array $middleware = [
        \App\Http\Middleware\ExampleMiddleware::class,

    protected array $middlewareAliases = [
        'auth' => \Phox\Foundation\Http\Middlewares\Authenticate::class,
    ];

    protected array $bootstrappers = [
        \Phox\Foundation\Bootstrap\LoadEnvironmentVariables::class,
        \Phox\Foundation\Bootstrap\LoadConfiguration::class,
        \Phox\Foundation\Bootstrap\HandleExceptions::class,
        \Phox\Foundation\Bootstrap\RegisterProviders::class,
        \Phox\Foundation\Bootstrap\RegisterFacades::class,
    ];

    public function handle(RequestInterface $request): ResponseInterface
    {
        $response = $this->app->handle($request->getURI());
        return $response ?: $this->app['response'];
    }
}