<?php

namespace Krak\Job;

use Krak\Cargo;

class Kernel extends Cargo\Container\ContainerDecorator implements Dispatch
{
    public function __construct(Cargo\Container $c = null) {
        parent::__construct($c ?: Cargo\container([], $auto_wire = true));
        Cargo\register($this, new JobServiceProvider());
    }

    public function wrap(Job $job) {
        return $this[Dispatch::class]->wrap($job);
    }
    public function dispatch(Job $job) {
        return $this[Dispatch::class]->dispatch($job);
    }
    public function dispatchWrappedJob(WrappedJob $wrapped) {
        return $this[Dispatch::class]->dispatchWrappedJob($wrapped);
    }

    public function config(array $config) {
        $this['krak.job.config'] = $config;
    }

    public function queueManager($def) {
        Cargo\wrap($this, Queue\QueueManager::class, $def);
    }
}
