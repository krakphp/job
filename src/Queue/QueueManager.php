<?php

namespace Krak\Job\Queue;

/** creates queues to store jobs in the medium of the queue provider itself */
interface QueueManager {
    public function createQueue($name, array $opts = []);
    public function removeQueue($name);
    public function getQueue($name);
}
