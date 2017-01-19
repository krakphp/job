<?php

namespace Krak\Job\Console;

use Krak\Job,
    Symfony\Component\Console\Command\Command,
    Symfony\Component\Console\Input,
    Symfony\Component\Console\Output;

class WorkerCommand extends Command
{
    protected function configure() {
        $this->setName('job:worker')
            ->setDescription('Starts a worker process to handle a job. Expects the serialized job in stdin and will output serialized result')
            ->setHidden(true);
    }

    protected function execute(Input\InputInterface $input, Output\OutputInterface $output) {
        $worker = $this->getHelper('krak_job')->getKernel()->createWorker();
        $output->write($worker->work(file_get_contents('php://stdin')));
    }
}
