<?php

namespace Krak\Job\Console;

use Krak\Job,
    Symfony\Component\Console\Command\Command,
    Symfony\Component\Console\Input,
    Symfony\Component\Console\Output,
    Symfony\Component\Console\Logger\ConsoleLogger,
    Symfony\Component\Yaml;

class SchedulerCommand extends Command
{
    protected function configure() {
        $this->setName('job:scheduler')
            ->setDescription('Starts a scheduler to run the schedule loop')
            ->setHidden(true);
    }

    protected function execute(Input\InputInterface $input, Output\OutputInterface $output) {
        if (!$input->getStream()) {
            $input->setStream(fopen('php://stdin', 'r'));
        }
        $options = json_decode(stream_get_contents($input->getStream()), true);

        $scheduler = $this->getHelper('krak_job')->getKernel()->createScheduler();

        $logger = new ConsoleLogger($output);
        $logger = new PrefixLogger($logger, $this->getPrefixFromOptions($options));

        $logger->info("Starting Scheduler");
        $scheduler->run($output, $logger, $options);
        $logger->info("Starting Stopped");
    }

    private function getPrefixFromOptions(array $options) {
        if (isset($options['name'])) {
            return $options['name'] . ': ';
        } else if (isset($options['queue'])) {
            return 'Queue Scheduler - ' . $options['queue'] . ': ';
        } else {
            return '';
        }
    }
}
