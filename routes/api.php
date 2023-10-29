<?php

$router = new \Framework\Routing\Router();

$router->group([], function (\Framework\Routing\Router $router) {
    $router->post('/', '\App\Http\Controllers\ExampleController@index')->name('example.index');
    $router->post('/version', '\App\Http\Controllers\ExampleController@version')->name('example.version');
});

$router->group(['prefix' => 'auth'], function (\Framework\Routing\Router $router) {
    $router->post('/login/nonce', '\App\Http\Controllers\Auth\LoginController@nonce')->name('auth.login.nonce');
    $router->post('/login', '\App\Http\Controllers\Auth\LoginController@store')->name('auth.login.store');

    $router->post('/register/nonce', '\App\Http\Controllers\Auth\RegisterController@nonce')->name('auth.register.nonce');
    $router->post('/register', '\App\Http\Controllers\Auth\RegisterController@store')->name('auth.register.store');
});

return $router;
