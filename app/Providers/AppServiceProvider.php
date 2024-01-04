<?php

namespace App\Providers;

use Phalcon\Di\DiInterface;
use Phalcon\Di\ServiceProviderInterface;
use Phox\Foundation\Micro as Application;

class AppServiceProvider implements ServiceProviderInterface
{
    public function register(Application|DiInterface $app): void
    {
        $app->bind(\App\Contracts\Repository\UserContract::class,
            \App\Repository\UserRepository::class);
    }
}
