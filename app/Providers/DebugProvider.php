<?php

namespace App\Providers;

use Phalcon\Di\DiInterface;
use Phalcon\Di\ServiceProviderInterface;
use Phox\Foundation\Micro as Application;

class DebugProvider implements ServiceProviderInterface
{
    public function register(Application|DiInterface $app): void
    {
        if (!class_exists(\Whoops\Run::class) || !config('app.debug')) {
            return;
        }

        $whoops = new \Whoops\Run();
        $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler());
        $whoops->register();
        // round(microtime(true) - APP_START, 4).'ms'
    }
}
