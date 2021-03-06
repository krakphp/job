<?php

namespace Krak\Job;

/** Workers do the work of connecting the queue to the consumer */
class Worker
{
    private $consume;

    public function __construct($consume) {
        $this->consume = $consume;
    }

    public function work($input) {
        return serialize(array_map($this->consume, unserializeJobs($input)));
    }
}
