<?php

namespace Krak\Job\Queue;

use Krak\Job;

interface Queue {
    /** returns the name of the queue */
    public function getName();
    /** push a job onto the queue */
    public function enqueue(Job\Job $job);
    /** take a job off of the queue */
    public function dequeue();
    /** job failed, needs to be stored in failed queue */
    public function fail(Job\Job $job);
    /** job was completed and can be removed completely from queue */
    public function complete(Job\Job $job);
}
