<?php

$router = new \Phox\Routing\Router();

$router->post('/version', '\App\Http\Controllers\ExampleController@version')->name('example.version');

$router->group(['middleware' => ['auth']], function (\Phox\Routing\Router $router) {
    $router->post('/', '\App\Http\Controllers\ExampleController@index')->name('example.index');
});

$router->group(['prefix' => 'auth'], function (\Phox\Routing\Router $router) {
    // Login
    $router->post('/login/nonce', '\App\Http\Controllers\Auth\LoginController@nonce')->name('auth.login.nonce');
    $router->post('/login', '\App\Http\Controllers\Auth\LoginController@store')->name('auth.login.store');

    // Register
    $router->post('/register/nonce', '\App\Http\Controllers\Auth\RegisterController@nonce')->name('auth.register.nonce');
    $router->post('/register', '\App\Http\Controllers\Auth\RegisterController@store')->name('auth.register.store');
});

return $router;
