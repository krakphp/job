<?php

namespace Krak\Job\Queue\Sync;

use Krak\Job;

class SyncQueue extends Job\Queue\AbstractQueue
{
    private $worker;

    public function __construct($name, Job\Worker $worker) {
        parent::__construct($name);
        $this->worker = $worker;
    }

    /** push a job onto the queue */
    public function enqueue(Job\WrappedJob $job) {
        $res = unserialize($this->worker->work((string) $job->withQueueProvider('sync')));
        if ($res->isFailed()) {
            throw new \RuntimeException("Job Failed - " . json_encode($res, JSON_PRETTY_PRINT));
        }
    }
    /** take a job off of the queue */
    public function dequeue() {
        /* noop */
    }
    /** job was completed and can be removed completely from queue */
    public function complete(Job\WrappedJob $job) {
        /* noop */
    }
}
