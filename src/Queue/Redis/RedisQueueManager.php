<?php

namespace Krak\Job\Queue\Redis;

use Krak\Job\Queue;

class RedisQueueManager implements Queue\QueueManager
{
    private $redis;

    public function __construct(\Predis\ClientInterface $redis) {
        $this->redis = $redis;
    }

    public function createQueue($name, array $opts = []) {
        // noop - queues are automatically created with redis
    }

    public function removeQueue($name) {
        $this->redis->del($name);
        $this->redis->del($name . '-processing');
    }

    public function getQueue($name) {
        return new RedisQueue(
            $this->redis,
            $name,
            $name,
            $name . '-processing'
        );
    }
}
