<?php

namespace Krak\Job;

use Krak\Cargo;
use Psr\SimpleCache\CacheInterface;

class Kernel extends Cargo\Container\ContainerDecorator implements Queue\QueueManager
{
    public function __construct(Cargo\Container $c = null) {
        parent::__construct($c ?: Cargo\container([], $auto_wire = true));
        Cargo\register($this, new JobServiceProvider());
    }

    public function dispatch(Job $job = null) {
        if (!$job) {
            return $this[Dispatch::class];
        }

        return $this[Dispatch::class]->dispatch($job);
    }

    public function config(array $config) {
        $this['krak.job.config'] = $config;
    }

    public function queueManager($def) {
        Cargo\wrap($this, Queue\QueueManager::class, $def);
    }

    public function createQueue($name, array $opts = []) {
        return $this[Queue\QueueManager::class]->createQueue($name, $opts);
    }

    public function removeQueue($name) {
        return $this[Queue\QueueManager::class]->removeQueue($name);
    }

    public function getQueue($name) {
        return $this[Queue\QueueManager::class]->getQueue($name);
    }

    public function isCacheEnabled() {
        return $this->has(CacheInterface::class);
    }
}
