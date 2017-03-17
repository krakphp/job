<?php

namespace Krak\Job\Queue;

use Krak\Job;

abstract class AbstractQueue implements Queue {
    protected $name;
    public function __construct($name) {
        $this->name = $name;
    }
    public function getName() {
        return $this->name = $name;
    }

    abstract public function enqueue(Job\WrappedJob $job);
    abstract public function dequeue();
    abstract public function complete(Job\WrappedJob $job);
}
