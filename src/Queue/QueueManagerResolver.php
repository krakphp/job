<?php

namespace Krak\Job\Queue;

use Krak\Cargo\Container;

class QueueManagerResolver
{
    private $container;

    public function __construct(Container $container) {
        $this->container = $container;
    }

    public function resolveQueueManager($queue_provider) {
        switch ($queue_provider) {
        case 'doctrine': return $this->container[Doctrine\DoctrineQueueManager::class];
        case 'redis': return $this->container[Redis\RedisQueueManager::class];
        case 'sqs': return $this->container[Sqs\SqsQueueManager::class];
        case 'stub': return new Stub\StubQueueManager();
        case 'sync': return $this->container[Sync\SyncQueueManager::class];
        default:
            throw new \InvalidArgumentException('Invalid Queue Provider given.');
        }
    }
}
