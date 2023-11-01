<?php

namespace App\Providers;

use Phox\Foundation\Micro as Application;
use Phox\Support\CarbonLite;
use Phalcon\Di\DiInterface;
use Phalcon\Di\ServiceProviderInterface;

class AppServiceProvider implements ServiceProviderInterface
{
    public function register(Application|DiInterface $app): void
    {
        $app->bind(
            \App\Contracts\Repository\UserContract::class,
            \App\Repository\UserRepository::class
        );
    }
}