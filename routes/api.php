<?php

use Phalcon\Mvc\Micro\Collection as MicroCollection;

$router = new MicroCollection();

$router->setHandler('App\Http\Controllers\IndexController')
    ->setLazy(true)
    ->get('/', 'index');

return $router;
