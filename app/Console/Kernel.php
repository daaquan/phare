<?php

namespace App\Console;

use Phare\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     */
    protected array $commands = [
        \App\Console\Commands\AboutCommand::class,
        \Phare\Console\Commands\KeyGenerateCommand::class,
        \App\Console\Commands\Migrate\MigrateCommand::class,
        \App\Console\Commands\Creator\MakeMigrationCommand::class,
        \Phare\Console\Commands\QueueWorkCommand::class,
        \App\Console\Commands\ServeCommand::class,
    ];

    /**
     * The bootstrap classes for the application.
     */
    protected array $bootstrappers = [
        \Phare\Foundation\Bootstrap\LoadEnvironmentVariables::class,
        \Phare\Foundation\Bootstrap\LoadConfiguration::class,
        \Phare\Foundation\Bootstrap\HandleExceptions::class,
        \Phare\Foundation\Bootstrap\RegisterProviders::class,
        \Phare\Foundation\Bootstrap\RegisterFacades::class,
    ];
}
