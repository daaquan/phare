<?php

// Boots the application the same way the test case does, starts the session,
// and prints PHP's effective session cookie parameters as JSON. Run in a
// separate process: within the Pest process a session is already active and
// PHP refuses to change cookie settings.

use Phalcon\Di\Di;
use Phare\Foundation\Bootstrap\HandleExceptions;
use Phare\Foundation\Bootstrap\LoadConfiguration;
use Phare\Foundation\Bootstrap\LoadEnvironmentVariables;
use Phare\Foundation\Bootstrap\RegisterFacades;
use Phare\Foundation\Bootstrap\RegisterProviders;

define('APP_RUNNING_UNIT_TEST', true);

$app = require __DIR__ . '/../../bootstrap/app.php';
$app->bootstrapWith([
    LoadEnvironmentVariables::class,
    LoadConfiguration::class,
    HandleExceptions::class,
    RegisterProviders::class,
    RegisterFacades::class,
]);
Di::setDefault($app->phalconDi());

$app->make('session');

echo json_encode([
    'params' => session_get_cookie_params(),
    'name' => session_name(),
    'strict' => ini_get('session.use_strict_mode'),
]);
