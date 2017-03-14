<?php

namespace Krak\Job\Queue\Stub;

use Krak\Job\Queue;

class StubQueueManager implements Queue\QueueManager
{

    public function createQueue($name, array $opts = []) {}

    public function removeQueue($name) {}

    public function getQueue($name) {
        return new StubQueue($name);
    }
}
