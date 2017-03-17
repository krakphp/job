<?php

namespace Krak\Job\Dispatch;

use Krak\Job;

class ProducerDispatch implements Job\Dispatch
{
    private $produce;
    private $builder_class;

    public function __construct($produce, $builder_class = Job\WrappedJobBuilder::class) {
        $this->produce = $produce;
        $this->builder_class = $builder_class;
    }

    public function wrap(Job\Job $job) {
        $cls = $this->builder_class;
        return new $cls($this, $job);
    }

    public function dispatch(Job\Job $job) {
        return $this->wrap($job)->dispatch();
    }

    public function dispatchWrappedJob(Job\WrappedJob $job) {
        $produce = $this->produce;
        return $produce($job);
    }
}
