<?php

namespace Krak\Job;

use Psr\Log,
    iter,
    Symfony\Component\Process;

class Scheduler
{
    private $queue_manager;
    private $logger;
    private $running;
    private $options;
    private $loop_state;

    public function __construct(Queue\QueueManager $queue_manager, array $options = [], Log\LoggerInterface $logger = null) {
        $this->queue_manager = $queue_manager;
        $this->options = $options + [
            'sleep_interval' => 5,
            // 'max_jobs' => 5,
        ];
        $this->logger = $logger ?: new Log\NullLogger();
        $this->running = [];
    }

    public function run(array $options = []) {
        $i = 0;

        $options = $options + $this->options;
        if (!isset($options['worker_cmd'])) {
            $options['worker_cmd'] = $options['bin'] . ' job:worker';
        }

        $this->loop_state = [
            $this->queue_manager->getQueue($options['queue']),
            $options,
        ];

        while($this->tick($i)) {
            $i += 1;
        }
    }

    private function tick($i) {
        list($queue, $options) = $this->loop_state;

        $this->logger->debug('starting ' . $i);

        $this->logger->info("cleaning procs");
        $this->cleanProcs();

        $this->logger->info("starting job");
        $this->pollQueue($queue);

        $this->logger->info('going to sleep for ' . $options['sleep_interval'] . ' seconds');
        sleep($this->options['sleep_interval']);

        return true;
    }

    private function cleanProcs() {
        list($queue, $options) = $this->loop_state;

        list($this->running, $finished) = iter\reduce(function($acc, $tup) {
            list($running, $finished) = $acc;
            list($job, $proc) = $tup;

            if ($proc->isRunning()) {
                $running[] = $tup;
            } else {
                $finished[] = $tup;
            }

            return [$running, $finished];
        }, $this->running, [[], []]);

        foreach ($finished as $tup) {
            list($job, $proc) = $tup;
            if (!$proc->isSuccessful()) {
                $this->logger->error('Job Process Failed! - ' . $job->name . ' - ' . $proc->getErrorOutput());
                $queue->fail($job);
                continue;
            }

            $res = unserialize($proc->getOutput());
            if (!$res || !$res instanceof Result) {
                $this->logger->error('Worker returned invalid output: ' . $proc->getOutput());
                $queue->fail($job);
                continue;
            }
            $this->logger->info('Job Finished - ' . $job->name . ' - ' . $res->status);
            $this->logger->info('Job Payload: ' . json_encode($res->payload));
            if ($res->isFailed()) {
                $queue->fail($job);
            } else {
                $queue->complete($job);
            }
        }
    }

    public function pollQueue() {
        list($queue, $options) = $this->loop_state;
        while ($job = $queue->dequeue()) {
            $proc = new Process\Process($options['worker_cmd']);
            $proc->setInput(serialize($job));
            $proc->start();
            $this->logger->debug("launching job...");
            $this->running[] = [$job, $proc];
        }
    }

    public static function factory(Queue\QueueManager $queue_manager, array $options = []) {
        return function(Log\LoggerInterface $logger) use ($queue_manager, $options) {
            return new self($queue_manager, $options, $logger);
        };
    }
}
