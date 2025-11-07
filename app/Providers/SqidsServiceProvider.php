<?php

namespace App\Providers;

use App\Services\SqidsGenerator;
use Phalcon\Di\DiInterface;
use Phalcon\Di\ServiceProviderInterface;
use Phare\Foundation\AbstractApplication as Application;

class SqidsServiceProvider implements ServiceProviderInterface
{
    public function register(Application|DiInterface $app): void
    {
        $app->singleton('sqids', fn () => new SqidsGenerator());
    }
}
