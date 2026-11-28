<?php

namespace App\Http;

use App\Http\Middleware\Authenticate;
use App\Http\Middleware\EnsureEmailIsVerified;
use App\Http\Middleware\EnsureUserIsAdmin;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\RedirectIfAuthenticated;
use App\Http\Middleware\RequirePassword;
use App\Http\Middleware\ThrottlePasswordChecks;
use App\Http\Middleware\ThrottleRequests;
use App\Http\Middleware\ThrottleTwoFactor;
use App\Http\Middleware\ValidateSessionVersion;
use App\Http\Middleware\VerifyCsrfToken;
use Phalcon\Http\RequestInterface;
use Phalcon\Http\ResponseInterface;
use Phare\Foundation\Bootstrap\HandleExceptions;
use Phare\Foundation\Bootstrap\LoadConfiguration;
use Phare\Foundation\Bootstrap\RegisterFacades;
use Phare\Foundation\Bootstrap\RegisterProviders;
use Phare\Foundation\Http\Kernel as HttpKernel;
use Phare\Routing\Middleware\CorsMiddleware;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     */
    protected array $middlewares = [
        // \App\Http\Middleware\CheckForMaintenanceMode::class,
        CorsMiddleware::class,
    ];

    protected array $middlewareGroups = [
        'web' => [
            // \App\Http\Middleware\EncryptCookies::class,
            VerifyCsrfToken::class,
            ValidateSessionVersion::class,
            HandleInertiaRequests::class,
        ],

        'api' => [
            // \App\Http\Middleware\RequestFormatMiddleware::class,
        ],
    ];

    protected array $routeMiddleware = [
        'auth' => Authenticate::class,
        'throttle' => ThrottleRequests::class,
        'throttle.password' => ThrottlePasswordChecks::class,
        'throttle.two-factor' => ThrottleTwoFactor::class,
        'guest' => RedirectIfAuthenticated::class,
        'verified' => EnsureEmailIsVerified::class,
        'password.confirm' => RequirePassword::class,
        'admin' => EnsureUserIsAdmin::class,
    ];

    /**
     * The bootstrap classes for the application.
     */
    protected array $bootstrappers = [
        LoadConfiguration::class,
        HandleExceptions::class,
        RegisterProviders::class,
        RegisterFacades::class,
    ];

    public function handle(RequestInterface $request): ResponseInterface
    {
        $response = $this->app->handle($request->getURI());

        return $response ?: $this->app['response'];
    }
}
