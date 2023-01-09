<?php

namespace App\Providers;

use Framework\Foundation\Application;
use Phalcon\Di\DiInterface;
use Phalcon\Di\ServiceProviderInterface;

class DebugProvider implements ServiceProviderInterface
{
    public function register(Application|DiInterface $di): void
    {
        if (!config('app.debug') || !class_exists(\Whoops\Run::class)) {
            return;
        }

        $whoops = new \Whoops\Run();
        $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler());
        $whoops->register();
        // round(microtime(true) - APP_START, 4).'ms'
    }
}