<?php

namespace Krak\Job\Queue\Redis;

use Krak\Job;

class RedisQueue extends Job\Queue\AbstractQueue
{
    private $redis;
    private $queue_name;
    private $processing_queue_name;
    private $failed_queue_name;

    public function __construct(\Predis\ClientInterface $redis, $name, $queue_name, $processing_queue_name, $failed_queue_name) {
        parent::__construct($name);
        $this->redis = $redis;
        $this->queue_name = $queue_name;
        $this->processing_queue_name = $processing_queue_name;
        $this->failed_queue_name = $failed_queue_name;
    }

    public function enqueue(Job\WrappedJob $job) {
        return $this->redis->lpush($this->queue_name, $job);
    }

    public function dequeue() {
        $job = $this->redis->rpoplpush($this->queue_name, $this->processing_queue_name);
        if (!$job) {
            return;
        }
        return Job\WrappedJob::fromString($job);
    }

    public function complete(Job\WrappedJob $job) {
        $this->redis->lrem($this->processing_queue_name, 1, $job);
    }
}
