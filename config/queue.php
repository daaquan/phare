<?php

return [
    'default' => env('QUEUE_CONNECTION', 'beanstalkd'),

    'connections' => [
        'beanstalkd' => [
            'driver' => 'beanstalkd',
            'host' => env('BEANSTALKD_HOST', 'localhost'),
            'port' => env('BEANSTALKD_PORT', 11300),
            'queue' => env('BEANSTALKD_QUEUE', 'default'),
            'retry_after' => 90,
        ],
    ],
];
