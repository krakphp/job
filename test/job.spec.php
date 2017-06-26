<?php

Mockery::globalHelpers();

class TestJob implements Krak\Job\Job {
    public $id;
}

describe('Krak Job', function() {
    describe('ProcessManager', function() {
        require_once __DIR__ . '/process-manager.php';
    });
    describe('Kernel', function() {
        require_once __DIR__ . '/kernel.php';
    });
    describe('Queue', function() {
        require_once __DIR__ . '/queue.php';
    });
    describe('WrappedJob', function() {
        describe('->__toString', function() {
            $job = new TestJob();
            $job->id = 1;
            $wrapped = new Krak\Job\WrappedJob($job, ['name' => 1]);
            assert((string) $wrapped == json_encode([
                "job" => serialize($job),
                "payload" => ['name' => 1]
            ]));
        });
        describe('::fromString', function() {
            $job = new TestJob();
            $job->id = 1;
            $wrapped = new Krak\Job\WrappedJob($job, ['name' => 1]);
            $wrapped = Krak\Job\WrappedJob::fromString((string) $wrapped);
            assert($wrapped->job instanceof TestJob && $wrapped->payload['name'] == 1);
        });
    });
});
