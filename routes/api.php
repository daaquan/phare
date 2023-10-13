<?php

$router = new \Framework\Routing\Router();

$router->group([], function ($router) {
    $router->post('/', '\App\Http\Controllers\IndexController@index');
    $router->post('/version', '\App\Http\Controllers\IndexController@version');
});

return $router;
