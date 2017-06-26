<?php

namespace Krak\Job\TestFixtures;

use Krak\Job;
use SplStack;

class EchoJob implements Job\Job
{
    private $id;
    public function __construct($id) {
        $this->id = $id;
    }

    public function handle(SplStack $stack, Job\WrappedJob $job) {
        sleep($this->id);
        return Job\complete([
            'id' => $this->id,
            'count' => $stack->count(),
            'payload' => $job->payload,
        ]);
    }
}
