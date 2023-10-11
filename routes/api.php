<?php

$router = new \Framework\Routing\Micro\Route();

$router->setHandler('App\Http\Controllers\IndexController')
    ->setLazy(true)
    ->get('/', 'index')
    ->get('/version', 'version');

return $router;
