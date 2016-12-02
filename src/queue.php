<?php

namespace Krak\Job;

/** factory for creating queue managers */
function createQueueManager(...$args) {
    if ($args[0] instanceof \Predis\ClientInterface) {
        return new Queue\Redis\RedisQueueManager($args[0]);
    }

    throw new \InvalidArgumentException('Unable to determine queue type to create');
}
