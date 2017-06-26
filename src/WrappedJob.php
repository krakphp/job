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

    public function has($key) {
        return array_key_exists($key, $this->payload);
    }

    public function get($key, $default = null) {
        if (!$this->has($key)) {
            return $default;
        }

        return $this->payload[$key];
    }

    public function withName($name) {
        return $this->withAddedPayload(['name' => $name]);
    }
    public function getName() {
        return $this->get('name');
    }

    public function withQueue($name) {
        return $this->withAddedPayload(['queue' => $name]);
    }
    public function getQueue() {
        return $this->get('queue');
    }

    public function withDelay($delay) {
        return $this->withAddedPayload(['delay' => $delay]);
    }
    public function getDelay() {
        return $this->get('delay');
    }

    public function withQueueProvider($queue_provider) {
        return $this->withAddedPayload(['queue_provider' => $queue_provider]);
    }
    public function getQueueProvider() {
        return $this->get('queue_provider');
    }
}
