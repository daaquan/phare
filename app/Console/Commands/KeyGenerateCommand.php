<?php

namespace App\Console\Commands;

use Phox\Console\Command;
use Phox\Support\Facades\Log;
use Random\RandomException;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'key:generate', description: 'Generate the application key.')]
class KeyGenerateCommand extends Command
{
    public function handle()
    {
        $envPath = base_path('.env');
        $envContent = file_get_contents($envPath);

        if (preg_match('/^APP_KEY=.*$/m', $envContent, $matches)) {
            try {
                $key = base64_encode(random_bytes(32));
            } catch (RandomException $e) {
                Log::error($e->getMessage());

                $this->output->writeError('Failed to generate application key.');

                return;
            }

            $envContent = str_replace($matches[0], 'APP_KEY=base64:' . $key, $envContent);
            file_put_contents($envPath, $envContent);

            $this->output->writeInfo('Application key set successfully.');
        } else {
            $this->output->writeError('APP_KEY not found in .env file.');
        }
    }
}
