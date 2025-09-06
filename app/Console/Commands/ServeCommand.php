<?php

namespace App\Console\Commands;

use Phare\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'serve', description: 'Serve the application on the PHP development server')]
class ServeCommand extends Command
{
    protected ?string $signature = 'serve {--host=127.0.0.1 : The host address to serve the application on} {--port=8000 : The port to serve the application on}';

    public function handle()
    {
        $host = $this->option('host');
        $port = $this->option('port');

        $this->info("Starting server on http://{$host}:{$port}");

        $command = "php -S {$host}:{$port} -t public";

        $this->line("Command: {$command}");

        // Execute the command
        passthru($command);
    }
}