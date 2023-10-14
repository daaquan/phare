<?php

$router = new \Framework\Routing\Router();

$router->group([], function (\Framework\Routing\Router $router) {
    $router->post('/', '\App\Http\Controllers\ExampleController@index')->name('example.index');
    $router->post('/version', '\App\Http\Controllers\ExampleController@version')->name('example.version');
});

return $router;
