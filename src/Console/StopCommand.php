<?php

namespace Krak\Job\Console;

use Krak\Job,
    Symfony\Component\Console\Command\Command,
    Symfony\Component\Console\Input,
    Symfony\Component\Console\Output,
    Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Filesystem\LockHandler;

class StopCommand extends Command
{
    protected function configure() {
        $this->setName('job:stop')
            ->setDescription('Stops a running scheduler.')
            ->addArgument(
                'instance-name',
                Input\InputArgument::OPTIONAL,
                'An identifier for the scheduler instance. Defaults to "scheduler"'
            );
    }

    protected function execute(Input\InputInterface $input, Output\OutputInterface $output) {
        $output->setVerbosity(Output\OutputInterface::VERBOSITY_VERY_VERBOSE);
        $instance_name = $input->getArgument('instance-name') ?: 'scheduler';

        $kernel = $this->getHelper('krak_job')->getKernel();
        if (!$kernel->isCacheEnabled()) {
            $output->writeln('<error>Cannot stop a scheduler without cache enabled.</error>');
            return;
        }

        $scheduler_control = $kernel[Job\SchedulerControl::class];
        $scheduler_control->stopScheduler($instance_name, new ConsoleLogger($output));
    }
}
