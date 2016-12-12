<?php

namespace Krak\Job\Console;

use Krak\Job,
    Symfony\Component\Console\Command\Command,
    Symfony\Component\Console\Input,
    Symfony\Component\Console\Output;

class SchedulerCommand extends Command
{
    private $scheduler_factory;

    public function __construct($scheduler_factory) {
        parent::__construct();
        $this->scheduler_factory = $scheduler_factory;
    }

    protected function configure() {
        $this->setName('job:scheduler')
            ->setDescription('Starts a scheduler to pull queue and start workers');
    }

    protected function execute(Input\InputInterface $input, Output\OutputInterface $output) {
        $options = json_decode(file_get_contents('php://stdin'), true);

        $scheduler_factory = $this->scheduler_factory;
        $scheduler = $scheduler_factory();

        $output->writeln('<info>Starting Scheduler</info>');
        $scheduler->run($options);
        $output->writeln('<info>Scheduler Stopped</info>');
    }

    private function getBinFromArgv($argv) {
        $bin = $argv[0];
        if (strpos($bin, './') === 0) {
            return $bin;
        }

        return "php " . $bin;
    }
}
