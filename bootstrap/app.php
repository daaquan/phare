<?php

require_once __DIR__ . '/../vendor/autoload.php';

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

$app = \App\ApplicationFactory::createApplication();

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
    \Phox\Contracts\Http\Kernel::class,
    \App\Http\Kernel::class
);

$app->singleton(
    \Phox\Contracts\Console\Kernel::class,
    \App\Console\Kernel::class
);

$app->singleton(
    \Phox\Contracts\Debug\ExceptionHandler::class,
    \App\Exceptions\Handler::class
);

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

return $app;
