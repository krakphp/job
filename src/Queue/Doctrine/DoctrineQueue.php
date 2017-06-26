<?php

namespace Krak\Job\Queue\Doctrine;

use Krak\Job;

class DoctrineQueue extends Job\Queue\AbstractQueue
{
    private $repo;
    private $cached_jobs;

    public function __construct($name, JobRepository $repo) {
        parent::__construct($name);
        $this->repo = $repo;
        $this->cached_jobs = [];
    }

    /** push a job onto the queue */
    public function enqueue(Job\WrappedJob $job) {
        $this->repo->addJob($job, $this->name);
    }

    /** take a job off of the queue */
    public function dequeue() {
        if (!count($this->cached_jobs)) {
            $this->cached_jobs = $this->repo->getAvailableJobs($this->name);
        }
        if (!count($this->cached_jobs)) {
            return;
        }

        $job_row = array_shift($this->cached_jobs);
        $job = Job\WrappedJob::fromString($job_row['job'])->withAddedPayload([
            '_doctrine' => ['id' => $job_row['id']]
        ])->withQueueProvider('doctrine');
        $this->repo->processJob($job);
        return $job;
    }

    /** job was completed and can be removed completely from queue */
    public function complete(Job\WrappedJob $job) {
        $this->repo->completeJob($job);
    }
}
