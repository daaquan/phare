<?php

namespace App\Console\Commands;

use Framework\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;

#[AsCommand(name: 'about', description: 'About the console app.')]
class AboutCommand extends Command
{
    /**
     * @var string
     * @see https://patorjk.com/software/taag
     */
    private static string $logo = <<<LOGO
___ ___ ___ ____
| | | |/ __/  __|
| | | | |  | |_  
| | | | |  |  _| 
| |_| | |__| |__ 
 \___/ \___ \___/
_________________
LOGO;

    public function handle()
    {
        $this->output->writeInfo(static::$logo);
        $this->output->write('ver.'.app()->version());
    }

    protected function configure()
    {
        $this
            ->addArgument('password', InputArgument::REQUIRED, 'User password')
            // the command help shown when running the command with the "--help" option
            ->setHelp('This command allows you to create a user...');
    }
}