<?php

namespace Krak\Job\Queue\Sync;

use Krak\Job;

class SyncQueueManager implements Job\Queue\QueueManager
{
    private $worker;

    public function __construct(Job\Worker $worker) {
        $this->worker = $worker;
    }

    public function createQueue($name, array $opts = []) {
        /* noop */
    }
    public function removeQueue($name) {
        /* noop */
    }
    public function getQueue($name) {
        return new SyncQueue($name, $this->worker);
    }
}
