<?php

namespace Krak\Job\Queue\Doctrine;

use Krak\Job;

class DoctrineQueueManager implements Job\Queue\QueueManager
{
    private $repo;

    public function __construct(JobRepository $repo) {
        $this->repo = $repo;
    }

    public function createQueue($name, array $opts = []) {
        /* noop */
    }
    public function removeQueue($name) {
        /* noop */
    }
    public function getQueue($name) {
        return new DoctrineQueue($name, $this->repo);
    }
}
