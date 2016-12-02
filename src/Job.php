<?php

namespace Krak\Job;

class Job
{
    public $name;
    public $payload;

    public function __construct($name, array $payload = []) {
        $this->name = $name;
        $this->payload = $payload;
    }

    public function withName($name) {
        $job = clone $this;
        $job->name = $name;
        return $job;
    }
    public function withPayload(array $payload) {
        $job = clone $this;
        $job->payload = $payload;
        return $job;
    }

    public function __toString() {
        return serialize($this);
    }

    public static function fromString($serialized) {
        return unserialize($serialized);
    }
}
