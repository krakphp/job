<?php

namespace Krak\Job\ProcessManager;

use Psr\Log\LoggerInterface;

/** Logging Decorator */
class LoggingProcessManager implements ProcessManager
{
    private $manager;
    private $logger;

    public function __construct(ProcessManager $manager, LoggerInterface $logger) {
        $this->manager = $manager;
        $this->logger = $logger;
    }

    public function launch($cmd, $input, $meta = null) {
        $this->logger->info('ProcessManager::launch', [
            'cmd' => $cmd,
            'input' => $input,
            'meta' => $meta,
        ]);
        $pid = $this->manager->launch($cmd, $input, $meta);
        $this->logger->info('ProcessManage::launch result', [
            'pid' => $pid
        ]);
        return $pid;
    }
    /** Go through and remove all of the jobs that have finished. Returns an array of 3-tuples
        `[$pid, $success, $output, $meta]`.

        - `$success` is flag of true for success or false for error.
        - `$output` is just the raw string content
    */
    public function reap() {
        $reaped = $this->manager->reap();
        $this->logger->info('ProcessManager::reap', [
            'reaped' => $reaped
        ]);
        return $reaped;
    }
    public function count() {
        return $this->manager->count();
    }
    public function getProcs() {
        return $this->procs;
    }
}
