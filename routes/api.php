<?php

$router = new \Framework\Routing\Micro\Route();

$router->setHandler('App\Http\Controllers\IndexController')
    ->setLazy(true)
    ->post('/', 'index')
    ->post('/version', 'version');

return $router;
