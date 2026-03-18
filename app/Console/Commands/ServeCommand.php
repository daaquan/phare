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

        if (!filter_var($host, FILTER_VALIDATE_IP)) {
            $this->error('Invalid host address.');

            return self::FAILURE;
        }

        if (!filter_var($port, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 65535]])) {
            $this->error('Invalid port number (1-65535).');

            return self::FAILURE;
        }

        $this->info("Starting server on http://{$host}:{$port}");

        passthru(sprintf('php -S %s:%d -t public', escapeshellarg($host), (int) $port));
    }
}