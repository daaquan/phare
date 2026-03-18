<?php

$router = new \Phare\Routing\Router();

$router->group(['middleware' => ['auth']], function (Phare\Routing\Router $router) {
    $router->post('/version', '\App\Http\Controllers\Api\IndexController@version')->name('version');
    $router->post('/', '\App\Http\Controllers\Api\IndexController@index')->name('index');
});

$router->group(['prefix' => 'auth', 'middleware' => ['throttle:5,1']], function (Phare\Routing\Router $router) {
    // Login
    $router->post('/login', '\App\Http\Controllers\Api\Auth\LoginController@store')->name('auth.login');

    // Register
    $router->post('/register', '\App\Http\Controllers\Api\Auth\RegisterController@store')->name('auth.register.store');
});

$router->group(['prefix' => 'auth', 'middleware' => ['auth']], function (Phare\Routing\Router $router) {
    $router->post('/logout', '\App\Http\Controllers\Api\Auth\LoginController@logout')->name('auth.logout');
});

return $router;
