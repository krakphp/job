<?php

namespace Krak\Job;

class WrappedJobBuilder
{
    private $dispatch;
    private $job;
    private $payload;

    public function __construct(Dispatch $dispatch, Job $job) {
        $this->dispatch = $dispatch;
        $this->job = $job;
        $this->payload = [];
    }

    public function onQueue($queue) {
        $this->payload['queue'] = $queue;
        return $this;
    }

    public function withName($name) {
        $this->payload['name'] = $name;
        return $this;
    }

    public function dispatch() {
        return $this->dispatch->dispatch(new WrappedJob($this->job, $this->payload));
    }
}
