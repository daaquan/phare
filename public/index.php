<?php

require_once __DIR__.'/../vendor/autoload.php';

$app = new Phalcon\Mvc\Micro;
$app->get('/', function () {
    echo 'index';
});
$app->handle($_SERVER['REQUEST_URI']);
