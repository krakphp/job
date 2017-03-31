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

    public function delay($seconds) {
        $this->payload['delay'] = $seconds;
        return $this;
    }

    public function with($key, $value) {
        $this->payload[$key] = $value;
        return $this;
    }

    public function dispatch() {
        return $this->dispatch->dispatchWrappedJob(new WrappedJob($this->job, $this->payload));
    }
}
