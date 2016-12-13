<?php

namespace Krak\Job\Console;

use Krak\Job,
    Symfony\Component\Console\Command\Command,
    Symfony\Component\Console\Input,
    Symfony\Component\Console\Output,
    Symfony\Component\Console\Logger\ConsoleLogger;

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

        if (!is_array($options)) {
            throw new \RuntimeException('Expected JSON array as stdin');
        }

        $bin = $this->getBinFromArgv($_SERVER['argv']);
        $options['worker_cmd'] = $bin . ' job:worker -vvv';
        $options['scheduler_cmd'] = $bin . ' job:scheduler -vvv';

        $scheduler_factory = $this->scheduler_factory;
        $scheduler = $scheduler_factory();

        $logger = new ConsoleLogger($output);
        $logger = new PrefixLogger($logger, $this->getPrefixFromOptions($options));

        $logger->info("Starting Scheduler");
        $scheduler->run($logger, $options);
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

    private function getBinFromArgv($argv) {
        $bin = $argv[0];
        if (strpos($bin, './') === 0) {
            return $bin;
        }

        return "php " . $bin;
    }
}
