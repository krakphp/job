<?php

namespace Krak\Job\Queue;

class CachedQueueManager implements QueueManager
{
    private $manager;
    private $queue_map;

    public function __construct(QueueManager $manager) {
        $this->manager = $manager;
        $this->queue_map = [];
    }

    public function createQueue($name, array $options = []) {
        return $this->manager->createQueue($name, $options);
    }
    public function removeQueue($name) {
        return $this->manager->removeQueue($name);
    }
    public function getQueue($name) {
        if (isset($this->queue_map[$name])) {
            return $this->queue_map[$name];
        }

        $q = $this->manager->getQueue($name);
        $this->queue_map[$name] = $q;
        return $q;
    }
}
