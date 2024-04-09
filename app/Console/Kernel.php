<?php

namespace App\Console;

use Phox\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     */
    protected array $commands = [
        \App\Console\Commands\AboutCommand::class,
        \App\Console\Commands\KeyGenerateCommand::class,
        \App\Console\Commands\Migrate\MigrateCommand::class,
        \App\Console\Commands\Creator\MakeMigrationCommand::class,
        \App\Console\Commands\QueueWorkCommand::class,
    ];

    /**
     * The bootstrap classes for the application.
     */
    protected array $bootstrappers = [
        \Phox\Foundation\Bootstrap\LoadEnvironmentVariables::class,
        \Phox\Foundation\Bootstrap\LoadConfiguration::class,
        \Phox\Foundation\Bootstrap\HandleExceptions::class,
        \Phox\Foundation\Bootstrap\RegisterProviders::class,
        \Phox\Foundation\Bootstrap\RegisterFacades::class,
    ];
}
