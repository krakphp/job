<?php

namespace Krak\Job\Console;

use Krak\Job,
    Symfony\Component\Console\Command\Command,
    Symfony\Component\Console\Input,
    Symfony\Component\Console\Output,
    Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Filesystem\LockHandler;

class ConsumeCommand extends Command
{
    protected function configure() {
        $this->setName('job:consume')
            ->setDescription('Consumes the jobs by starting scheudlers/workers according to config')
            ->addArgument(
                'instance-name',
                Input\InputArgument::OPTIONAL,
                'An identifier for the scheduler instance. Only one instance of a scheduler can be running at a time. Defaults to "scheduler"'
            );
    }

    protected function execute(Input\InputInterface $input, Output\OutputInterface $output) {
        $instance_name = $input->getArgument('instance-name') ?: 'scheduler';

        $lock_handler = new LockHandler($instance_name.'.lock');
        if (!$lock_handler->lock()) {
            $output->writeln('<error>A scheduler of this instance is already running.</error>');
            return 0;
        }
        $options = $this->getHelper('krak_job')->getKernel()->getConfig();
        $options['_instance_name'] = $instance_name;

        $argv = $_SERVER['argv'];
        $bin = $this->getBinFromArgv($argv);

        $options['_worker_cmd'] = $bin . ' job:worker ' . $this->getVerbosityString($output);
        $options['_scheduler_cmd'] = $bin . ' job:scheduler ' . $this->getVerbosityString($output);
        $options['_consume_cmd'] = $bin . ' ' . implode(' ', array_slice($argv, 1));
        $options['_root'] = true;
        $command = $this->getApplication()->find('job:scheduler');
        $command->run($this->createSchedulerInput($options), $output);
    }

    private function createSchedulerInput($options) {
        $stream = fopen("php://temp", "rw");
        fwrite($stream, json_encode($options));
        rewind($stream);
        $input = new Input\ArrayInput([]);
        $input->setStream($stream);
        return $input;
    }

    private function getVerbosityString($output) {
        switch ($output->getVerbosity()) {
        case Output\OutputInterface::VERBOSITY_QUIET: return '-q';
        case Output\OutputInterface::VERBOSITY_NORMAL: return '';
        case Output\OutputInterface::VERBOSITY_VERBOSE: return '-v';
        case Output\OutputInterface::VERBOSITY_VERY_VERBOSE: return '-vv';
        case Output\OutputInterface::VERBOSITY_DEBUG: return '-vvv';
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
