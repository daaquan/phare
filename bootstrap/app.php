<?php

require_once __DIR__.'/../vendor/autoload.php';

use Framework\Bootstrap\LoadEnvironmentVariables;
use Framework\Foundation\Application;

$basePath = $_ENV['APP_BASE_PATH'] ?? dirname(__DIR__);
(new LoadEnvironmentVariables($basePath))->bootstrap();

date_default_timezone_set(env('APP_TIMEZONE', 'UTC'));

/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
|
| Here we will load the environment and create the application instance
| that serves as the central piece of this framework. We'll use this
| application as an "IoC" container and router for this framework.
|
*/

$app = new Application($basePath);

/*
|--------------------------------------------------------------------------
| Bind Important Interfaces
|--------------------------------------------------------------------------
|
| Next, we need to bind some important interfaces into the container so
| we will be able to resolve them when needed. The kernels serve the
| incoming requests to this application from both the web and CLI.
|
*/

$app->singleton(
    \Framework\Contracts\Http\Kernel::class,
    function () use ($app) {
        return new \App\Http\Kernel($app);
    }
);

/*
|--------------------------------------------------------------------------
| Register Config Files
|--------------------------------------------------------------------------
|
| Now we will register the "app" configuration file. If the file exists in
| your configuration directory it will be loaded; otherwise, we'll load
| the default version. You may register other files below as needed.
|
*/

$app->configure('app');

/*
|--------------------------------------------------------------------------
| Load The Application Routes
|--------------------------------------------------------------------------
|
| Next we will include the routes file so that they can all be added to
| the application. This will provide all the URLs the application
| can respond to, as well as the controllers that may handle them.
|
*/

$router = require __DIR__.'/../routes/api.php';
$app->mount($router);

return $app;