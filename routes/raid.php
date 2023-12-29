<?php

$router = new \Phox\Routing\Router();

$router->group(['prefix' => 'raid'], function (Phox\Routing\Router $router) {
    $router->get('/', '\App\Http\Controllers\Api\RaidController@index')->name('raid.index');
});

return $router;
