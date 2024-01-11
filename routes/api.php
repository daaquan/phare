<?php

$router = new \Phox\Routing\Router();

$router->post('/version', '\App\Http\Controllers\Api\IndexController@version')->name('version');

$router->group(['middleware' => ['auth']], function (Phox\Routing\Router $router) {
    $router->post('/', '\App\Http\Controllers\Api\IndexController@index')->name('index');
});

$router->group(['prefix' => 'auth'], function (Phox\Routing\Router $router) {
    // Login
    $router->post('/login/nonce', '\App\Http\Controllers\Api\Auth\LoginController@nonce')->name('auth.login.nonce');
    $router->post('/login', '\App\Http\Controllers\Api\Auth\LoginController@store')->name('auth.login');
    $router->post('/logout', '\App\Http\Controllers\Api\Auth\LoginController@logout')->name('auth.logout');

    // Register
    $router->post('/register/nonce', '\App\Http\Controllers\Api\Auth\RegisterController@nonce')->name('auth.register.nonce');
    $router->post('/register', '\App\Http\Controllers\Api\Auth\RegisterController@store')->name('auth.register.store');
});

return $router;
