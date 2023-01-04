<?php

require_once __DIR__.'/../vendor/autoload.php';

use App\Application;
use App\LoadEnvironmentVariables;

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
| Load The Application Routes
|--------------------------------------------------------------------------
|
| Next we will include the routes file so that they can all be added to
| the application. This will provide all the URLs the application
| can respond to, as well as the controllers that may handle them.
|
*/

return $app;