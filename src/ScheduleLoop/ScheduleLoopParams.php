<?php

namespace Krak\Job\ScheduleLoop;

class ScheduleLoopParams {
    public $queue_manager;
    public $process_manager;
    public $logger;
    public $iteration_count;
    public $options;

    public function has($key) {
        return array_key_exists($key, $this->options);
    }
    public function get($key, $default = null) {
        if (!$this->has($key)) {
            return $default;
        }

        return $this->options[$key];
    }

    /** returns a queue instance from the queue manager based off of the queue
        parameter */
    public function queue() {
        if (!$this->has('queue')) {
            throw new \InvalidArgumentException('Expected `queue` option');
        }

        return $this->queue_manager->getQueue($this->get('queue'));
    }
}
