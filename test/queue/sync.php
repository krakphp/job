<?php

use Krak\Job;

describe('SyncQueue', function() {
    describe('->enqueue', function() {
        it('runs the job in a worker', function() {
            $work_completed = false;
            $worker = new Job\Worker(function($job) use (&$work_completed) {
                $work_completed = true;
                assert($job->job->id === 1);
            });

            $q = new Job\Queue\Sync\SyncQueue('queue', $worker);
            $q->enqueue(new Job\WrappedJob(new Job\TestFixtures\AcmeJob(1)));

            assert($work_completed);
        });
    });
});
