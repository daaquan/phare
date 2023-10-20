<?php

namespace App\Console\Commands;

use Framework\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'about', description: 'Shows the console app version.')]
class AboutCommand extends Command
{
    /**
     * @var string
     * @see https://patorjk.com/software/taag
     */
    private static string $logo = <<<LOGO

   .-.-.
  /|6 6\\
 {/(_0_)\}
 _/ ^ ^ \ _
(/ /^\ \)-'
 ""' '" "'

LOGO;

    public function handle()
    {
        $this->output->write('Framework Console (' . \App::version() . ')');
        $this->output->writeComment(static::$logo);
    }

    protected function configure()
    {
        $this->setHelp('Show the brief information about the console app.');
    }
}