<?php

namespace Krak\Job;

use Psr\Log\LoggerInterface,
    iter,
    Symfony\Component\Console\Output\OutputInterface,
    Symfony\Component\Process;

use function Krak\Mw\compose;

class Scheduler
{
    private $process_manager;
    private $queue_manager;
    private $loop;

    public function __construct(ProcessManager\ProcessManager $process_manager, Queue\QueueManager $queue_manager, $loop) {
        $this->process_manager = $process_manager;
        $this->queue_manager = $queue_manager;
        $this->loop = $loop;
    }

    public function run(OutputInterface $output, LoggerInterface $logger, array $options) {
        $i = 0;

        $params = new ScheduleLoop\ScheduleLoopParams();
        $params->process_manager = $this->process_manager;
        $params->queue_manager = new Queue\CachedQueueManager($this->queue_manager);
        $params->logger = $logger;
        $params->output = $output;

        $params->options = $options;
        $params->iteration_count = $i;

        $loop = $this->loop;

        while($loop($params)) {
            $i += 1;
            $params->iteration_count = $i;
        }
    }
}
