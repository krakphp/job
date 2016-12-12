<?php

namespace Krak\Job;

use Psr\Log,
    iter,
    Symfony\Component\Process;

class Scheduler
{
    private $process_manager;
    private $queue_manager;
    private $loop;
    private $logger;

    public function __construct(ProcessManager\ProcessManager $process_manager, Queue\QueueManager $queue_manager, $loop, Log\LoggerInterface $logger = null) {
        $this->process_manager = $process_manager;
        $this->queue_manager = $queue_manager;
        $this->loop = $loop;
        $this->logger = $logger ?: new Log\NullLogger();
    }

    public function run(array $options) {
        $i = 0;

        $params = new ScheduleLoop\ScheduleLoopParams();
        $params->process_manager = $this->process_manager;
        $params->queue_manager = new Queue\CachedQueueManager($this->queue_manager);
        $params->logger = $this->logger;
        $params->options = $options;
        $params->iteration_count = $i;

        $loop = $this->loop;

        while($loop($params)) {
            $i += 1;
            $params->iteration_count = $i;
        }
    }
}
