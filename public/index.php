<?php

$app = require __DIR__.'/../bootstrap/app.php';

$router = require __DIR__.'/../routes/api.php';
$app->mount($router)
    ->handle($_SERVER['REQUEST_URI']);
