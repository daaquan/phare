<?php

use App\LoadEnvironmentVariables;

$app = require __DIR__.'/../bootstrap/app.php';

(new LoadEnvironmentVariables(
    dirname(__DIR__)
))->bootstrap();

$router = require __DIR__.'/../routes/api.php';
$app->mount($router)
    ->handle($_SERVER['REQUEST_URI']);
