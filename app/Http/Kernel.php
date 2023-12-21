<?php

namespace App\Http;

use Phalcon\Http\RequestInterface;
use Phalcon\Http\ResponseInterface;
use Phox\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     */
    protected array $middlewares = [];

    protected array $routeMiddleware = [
        'auth' => \Phox\Foundation\Http\Middlewares\Authenticate::class,
    ];

    /**
     * The bootstrap classes for the application.
     */
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
