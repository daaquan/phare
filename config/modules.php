<?php

return [
    'api' => [
        'prefix' => 'api',

        'providers' => [],

        'aliases' => [],
    ],

    'gmtool' => [
        'providers' => [
            \Phox\Providers\BladeViewProvider::class,
            \Phox\Providers\TranslateProvider::class,
        ],

        'aliases' => [],
    ],
];
