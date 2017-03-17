<?php

namespace Krak\Job\Queue\Stub;

use Krak\Job;

class StubQueue extends Job\Queue\AbstractQueue
{
    public function enqueue(Job\WrappedJob $job) {}

    public function dequeue() {}

    public function complete(Job\WrappedJob $job) {}
}
