<?php

namespace App\Console;

use Framework\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected array $commands = [
        \App\Console\Commands\AboutCommand::class,
        \App\Console\Commands\KeyGenerateCommand::class,
        \App\Console\Commands\Migrate\MigrateCommand::class,
    ];

    /**
     * The bootstrap classes for the application.
     */
    protected array $bootstrappers = [
        \Framework\Foundation\Bootstrap\LoadEnvironmentVariables::class,
        \Framework\Foundation\Bootstrap\LoadConfiguration::class,
        \Framework\Foundation\Bootstrap\HandleExceptions::class,
        \Framework\Foundation\Bootstrap\RegisterProviders::class,
        \Framework\Foundation\Bootstrap\RegisterFacades::class,
    ];
}