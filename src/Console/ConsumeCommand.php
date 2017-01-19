<?php

namespace Krak\Job\Console;

use Krak\Job,
    Symfony\Component\Console\Command\Command,
    Symfony\Component\Console\Input,
    Symfony\Component\Console\Output,
    Symfony\Component\Console\Logger\ConsoleLogger,
    Symfony\Component\Yaml\Yaml;

class ConsumeCommand extends Command
{
    protected function configure() {
        $this->setName('job:consume')
            ->setDescription('Consumes the jobs by starting scheudlers/workers according to config')
            ->addArgument(
                'config-path',
                Input\InputArgument::REQUIRED,
                'The path to the jobs.yml file'
            );
    }

    protected function execute(Input\InputInterface $input, Output\OutputInterface $output) {
        $config_path = $input->getArgument('config-path');
        $options = Yaml::parse(file_get_contents($config_path));

        $bin = $this->getBinFromArgv($_SERVER['argv']);
        $options['worker_cmd'] = $bin . ' job:worker ' .$this->getVerbosityString($output);
        $options['scheduler_cmd'] = $bin . ' job:scheduler ' . $this->getVerbosityString($output);

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
