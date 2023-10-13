<?php

$router = new \Framework\Routing\Router();

$router->group([], function (\Framework\Routing\Router $router) {
    $router->post('/', '\App\Http\Controllers\IndexController@index')->name('index');
    $router->post('/version', '\App\Http\Controllers\IndexController@version')->name('version');
});

return $router;
