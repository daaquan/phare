<?php

namespace App\Console\Commands\Migrate;

use Framework\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;

#[AsCommand(name: 'make:migration', description: 'Create a new migration file.')]
class MakeMigrationCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->addArgument('database', InputArgument::REQUIRED, 'The database connection to use. ex: game')
            ->addArgument('name', InputArgument::REQUIRED, 'The migration file name to be created. ex: create_users_table');
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $database = $this->input->getArgument('database');

        if (!$this->isDatabaseConfigured($database)) {
            $this->output->writeError('Database connection is not configured.');
            return self::FAILURE;
        }

        $migrationsDirectory = $this->getMigrationsDirectory($database);
        if (!$this->createDirectory($migrationsDirectory)) {
            $this->output->writeError(sprintf('Directory "%s" was not created', $migrationsDirectory));
            return self::FAILURE;
        }

        $filename = $this->createMigrationFile($database, $this->input->getArgument('name'));
        $this->output->write("<info>Created Migration:</> {$filename}");

        return self::SUCCESS;
    }

    protected function isDatabaseConfigured(string $database): bool
    {
        return config("database.connections.{$database}") !== null;
    }

    protected function getMigrationsDirectory(string $database): string
    {
        return \App::databasePath("migrations/$database");
    }

    protected function createDirectory(string $directory): bool
    {
        return is_dir($directory) || mkdir($directory, 0755, true) || is_dir($directory);
    }

    protected function createMigrationFile(string $database, string $name): string
    {
        $filename = date('Y_m_d_His') . '_' . $name;
        file_put_contents($this->getMigrationsDirectory($database) . "/{$filename}.sql", '');
        return $filename;
    }
}
