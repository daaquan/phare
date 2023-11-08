<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Phox\Console\Command;
use Pheanstalk\Pheanstalk;
use Pheanstalk\Values\TubeName;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputOption;

#[AsCommand(name: 'queue:work', description: 'Start processing jobs on the queue as a daemon')]
class QueueWorkCommand extends Command
{
    // Default retry limit
    private const DEFAULT_RETRY_LIMIT = 1;

    protected function configure(): void
    {
        $this->addOption('tries', null, InputOption::VALUE_OPTIONAL, 'Number of times to retry a job', self::DEFAULT_RETRY_LIMIT);
    }

    public function handle()
    {
        $connection = config('queue.default', 'beanstalkd');
        if (!$config = config("queue.connections.$connection")) {
            throw new \RuntimeException('Queue connection is not configured.');
        }

        $pheanstalk = Pheanstalk::create($config['host'], (int)$config['port']);

        $this->output->writeInfo("Processing jobs from the [<options=bold>{$config['queue']}</>] queue.");

        $retryLimit = (int)$this->input->getOption('tries');

        $tubeName = new TubeName($config['queue']);
        while ($pheanstalk->watch($tubeName)) {

            // this hangs until a Job is produced.
            $reserved = $pheanstalk->reserve();

            try {
                $jobData = unserialize($reserved->getData(), ['allowed_classes' => true]);

                $jobData['closure']->handle();

                // If it's going to take a long time, periodically
                // tell beanstalk we're alive to stop it rescheduling the job.
                $pheanstalk->touch($reserved);

                // eventually we're done, delete job.
                $pheanstalk->delete($reserved);

            } catch (\Throwable $exception) {
                // handle exception.
                if (isset($jobData['retry']) && $jobData['retry'] >= $retryLimit) {
                    $pheanstalk->delete($reserved);
                } else {
                    $jobData['retry'] = isset($jobData['retry']) ? $jobData['retry'] + 1 : 1;
                    $pheanstalk->release($reserved, Pheanstalk::DEFAULT_PRIORITY, 1);
                }

                $this->output->write('<error>' . $exception->getMessage() . '</>');
            }

            $now = date('Y-m-d H:i:s');
            $this->output->write("  <fg=gray>{$now}</> " . get_class($jobData['closure']) . " <fg=green>processed</>");
        }
    }
}
