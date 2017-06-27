<?php

Mockery::globalHelpers();

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
    describe('Pipeline', function() {
        require_once __DIR__ . '/pipeline/produce.php';
    });
    describe('WrappedJob', function() {
        describe('->__toString', function() {
            it('serializes itself to a string', function() {
                $job = new Krak\Job\TestFixtures\AcmeJob(1);
                $wrapped = new Krak\Job\WrappedJob($job, ['name' => 1]);
                assert((string) $wrapped == json_encode([
                    "job" => serialize($job),
                    "payload" => ['name' => 1]
                ]));
            });
        });
        describe('::fromString', function() {
            it('unserializes to a wrapped job', function() {
                $job = new Krak\Job\TestFixtures\AcmeJob(1);
                $wrapped = new Krak\Job\WrappedJob($job, ['name' => 1]);
                $wrapped = Krak\Job\WrappedJob::fromString((string) $wrapped);
                assert($wrapped->job instanceof Krak\Job\TestFixtures\AcmeJob && $wrapped->payload['name'] == 1);
            });
        });
    });
    describe('#serializeJobs', function() {
        it('serializes a set of jobs', function() {
            $a = new class() {
                public function __toString() {
                    return '1';
                }
            };
            $serialized = Krak\Job\serializeJobs([$a, clone $a]);
            assert($serialized == '["1","1"]');
        });
    });
    describe('#unseralizeJobs', function() {
        it('unserializes a set of jobs', function() {
            $job = new Krak\Job\TestFixtures\AcmeJob(1);
            $wrapped = new Krak\Job\WrappedJob($job);
            $serialized = Krak\Job\serializeJobs([$wrapped, $wrapped]);
            $unwrapped = Krak\Job\unserializeJobs($serialized);
            assert(count($unwrapped) == 2 && $unwrapped[1]->job->id == 1);
        });
    });
});
