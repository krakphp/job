<?php

namespace Krak\Job\Console;

use Krak\Job,
    Symfony\Component\Console\Command\Command,
    Symfony\Component\Console\Input,
    Symfony\Component\Console\Output,
    Symfony\Component\Console\Logger\ConsoleLogger;

class ScheduleCommand extends Command
{
    private $scheduler_factory;

    public function __construct($scheduler_factory) {
        parent::__construct();
        $this->scheduler_factory = $scheduler_factory;
    }

    protected function configure() {
        $this->setName('job:schedule')
            ->setDescription('Starts a scheduler to pull queue and start workers')
            ->addArgument(
                'queue',
                Input\InputArgument::REQUIRED,
                'the name of the queue to schedule workers'
            );
    }

    protected function execute(Input\InputInterface $input, Output\OutputInterface $output) {
        $argv = $_SERVER['argv'];

        $scheduler_factory = $this->scheduler_factory;
        $scheduler = $scheduler_factory(new ConsoleLogger($output));

        $output->writeln('<info>Starting Scheduler</info>');
        $scheduler->run([
            'bin' => $this->getBinFromArgv($argv),
            'queue' => $input->getArgument('queue')
        ]);
    }

    private function getBinFromArgv($argv) {
        $bin = $argv[0];
        if (strpos($bin, './') === 0) {
            return $bin;
        }

        return "php " . $bin;
    }
}
