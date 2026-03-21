<?php

namespace App\Http;

use Phalcon\Http\RequestInterface;
use Phalcon\Http\ResponseInterface;
use Phare\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     */
    protected array $middlewares = [
        //\App\Http\Middleware\CheckForMaintenanceMode::class,
        \Phare\Routing\Middleware\CorsMiddleware::class,
    ];

    protected array $middlewareGroups = [
        'web' => [
            //\App\Http\Middleware\EncryptCookies::class,
        ],

        'api' => [
            //\App\Http\Middleware\RequestFormatMiddleware::class,
        ],
    ];

    protected array $routeMiddleware = [
        'auth' => \Phare\Auth\Middleware\Authenticate::class,
        'throttle' => \Phare\Middleware\ThrottleRequests::class,
    ];

    /**
     * The bootstrap classes for the application.
     */
    protected array $bootstrappers = [
        \Phare\Foundation\Bootstrap\LoadConfiguration::class,
        \Phare\Foundation\Bootstrap\HandleExceptions::class,
        \Phare\Foundation\Bootstrap\RegisterProviders::class,
        \Phare\Foundation\Bootstrap\RegisterFacades::class,
    ];

    public function handle(RequestInterface $request): ResponseInterface
    {
        $response = $this->app->handle($request->getURI());

        return $response ?: $this->app['response'];
    }
}
