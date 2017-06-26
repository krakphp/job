<?php

namespace Krak\Job;

/** Wrapped Job used to transport jobs */
class WrappedJob
{
    public $job;
    public $payload;

    public function __construct(Job $job, array $payload = []) {
        $this->job = $job;
        $this->payload = $payload;
    }

    public function withPayload(array $payload) {
        $wrapped = clone $this;
        $wrapped->payload = $payload;
        return $wrapped;
    }

    public function withAddedPayload(array $payload) {
        $payload = $payload + $this->payload;
        return $this->withPayload($payload);
    }

    public function __toString() {
        return json_encode([
            'job' => serialize($this->job),
            'payload' => $this->payload,
        ]);
    }

    public static function fromString($serialized) {
        $wrapped_job = json_decode($serialized, true);

        return new self(
            unserialize($wrapped_job['job']),
            $wrapped_job['payload']
        );
    }
}
