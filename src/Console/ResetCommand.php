<?php

namespace Krak\Job\Console;

use Krak\Job,
    Symfony\Component\Console\Command\Command,
    Symfony\Component\Console\Input,
    Symfony\Component\Console\Output,
    Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Filesystem\LockHandler;

class ResetCommand extends Command
{
    protected function configure() {
        $this->setName('job:reset')
            ->setDescription('Resets the scheduler cache.')
            ->addArgument(
                'instance-name',
                Input\InputArgument::OPTIONAL,
                'An identifier for the scheduler instance. Defaults to "scheduler"'
            );
    }

    protected function execute(Input\InputInterface $input, Output\OutputInterface $output) {
        $instance_name = $input->getArgument('instance-name') ?: 'scheduler';

        $kernel = $this->getHelper('krak_job')->getKernel();
        if (!$kernel->isCacheEnabled()) {
            $output->writeln('<error>Cannot reset scheduler cache without cache enabled.</error>');
            return;
        }

        $scheduler_control = $kernel[Job\SchedulerControl::class];
        $scheduler_control->resetSchedulerCache($instance_name, new ConsoleLogger($output));
    }
}
