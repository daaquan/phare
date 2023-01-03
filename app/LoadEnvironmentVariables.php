<?php

declare(strict_types=1);

namespace App;

use Dotenv\Dotenv;
use Dotenv\Exception\InvalidFileException;
use Symfony\Component\Console\Output\ConsoleOutput;

class LoadEnvironmentVariables
{
    /**
     * The directory containing the environment file.
     */
    protected string $filePath;

    /**
     * The name of the environment file.
     */
    protected ?string $fileName = null;

    /**
     * Create a new loads environment variables instance.
     *
     * @return void
     */
    public function __construct(string $path, ?string $name = null)
    {
        $this->filePath = $path;
        $this->fileName = $name;
    }

    /**
     * Setup the environment variables.
     *
     * If no environment file exists, we continue silently.
     */
    public function bootstrap(): void
    {
        try {
            $this->createDotenv()->safeLoad();
        } catch (InvalidFileException $e) {
            $this->writeErrorAndDie([
                'The environment file is invalid!',
                $e->getMessage(),
            ]);
        }
    }

    /**
     * Create a Dotenv instance.
     */
    protected function createDotenv(): Dotenv
    {
        return Dotenv::create(
            Env::getRepository(),
            $this->filePath,
            $this->fileName
        );
    }

    /**
     * Write the error information to the screen and exit.
     *
     * @param  array<string>  $errors
     */
    protected function writeErrorAndDie(array $errors): void
    {
        $output = (new ConsoleOutput())->getErrorOutput();

        foreach ($errors as $error) {
            $output->writeln($error);
        }

        exit(1);
    }
}
