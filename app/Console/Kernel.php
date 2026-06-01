<?php

namespace App\Console;

use App\Console\Commands\AboutCommand;
use App\Console\Commands\Creator\MakeMigrationCommand;
use App\Console\Commands\KeyGenerateCommand;
use App\Console\Commands\Migrate\MigrateCommand;
use App\Console\Commands\ServeCommand;
use Phare\Console\Commands\QueueWorkCommand;
use Phare\Console\Kernel as ConsoleKernel;
use Phare\Foundation\Bootstrap\HandleExceptions;
use Phare\Foundation\Bootstrap\LoadConfiguration;
use Phare\Foundation\Bootstrap\LoadEnvironmentVariables;
use Phare\Foundation\Bootstrap\RegisterFacades;
use Phare\Foundation\Bootstrap\RegisterProviders;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     */
    protected array $commands = [
        AboutCommand::class,
        KeyGenerateCommand::class,
        MigrateCommand::class,
        MakeMigrationCommand::class,
        QueueWorkCommand::class,
        ServeCommand::class,
    ];

    /**
     * The bootstrap classes for the application.
     */
    protected array $bootstrappers = [
        LoadEnvironmentVariables::class,
        LoadConfiguration::class,
        HandleExceptions::class,
        RegisterProviders::class,
        RegisterFacades::class,
    ];
}
