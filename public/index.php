<?php

define('APP_START', microtime(true));

/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
|
| First we need to get an application instance. This creates an instance
| of the application / container and bootstraps the application so it
| is ready to receive HTTP / Console requests from the environment.
|
*/

/** @var \Phox\Foundation\AbstractApplication $app */
$app = require __DIR__ . '/../bootstrap/app.php';

/*
|--------------------------------------------------------------------------
| Run The Application
|--------------------------------------------------------------------------
|
| Once we have the application, we can handle the incoming request
| through the kernel, and send the associated response back to
| the client's browser allowing them to enjoy the creative
| and wonderful application we have prepared for them.
|
*/

/** @var \Phox\Contracts\Http\Kernel $kernel */
$kernel = $app->make(\Phox\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = $app->make('request')
);

if (!$response->isSent()) {
    $response->send();
}

$kernel->terminate($request, $response);
