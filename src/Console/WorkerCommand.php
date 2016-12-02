<?php

namespace Krak\Job\Console;

use Krak\Job,
    Symfony\Component\Console\Command\Command,
    Symfony\Component\Console\Input,
    Symfony\Component\Console\Output;

class WorkerCommand extends Command
{
    private $worker_factory;

    public function __construct($worker_factory) {
        parent::__construct();
        $this->worker_factory = $worker_factory;
    }

    protected function configure() {
        $this->setName('job:worker')
            ->setDescription('Starts a worker process to handle a job. Expects the serialized job in stdin and will output serialized result');
    }

    protected function execute(Input\InputInterface $input, Output\OutputInterface $output) {
        $worker_factory = $this->worker_factory;
        $worker = $worker_factory();
        $output->write($worker->work(file_get_contents('php://stdin')));
    }
}
