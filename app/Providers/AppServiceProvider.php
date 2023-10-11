<?php

namespace App\Providers;

use Framework\Foundation\Micro as Application;
use Phalcon\Di\DiInterface;
use Phalcon\Di\ServiceProviderInterface;

class AppServiceProvider implements ServiceProviderInterface
{
    public function register(Application|DiInterface $app): void
    {
    }
}