<?php

use Krak\Job;

describe('->dispatch', function() {
    it('returns the dispatcher on no arguments', function() {
        $kernel = new Job\Kernel();
        assert($kernel->dispatch() instanceof Job\Dispatch);
    });
    it('dispatches a job', function() {
        $kernel = new Job\Kernel();
        $kernel[Job\Dispatch::class] = function() {
            return mock(Job\Dispatch::class, [
                'dispatch' => 1,
            ]);
        };

        $res = $kernel->dispatch(new class implements Job\Job {

        });

        assert($res === 1);
    });
});
describe('->createQueueProviderMap', function() {
    it('creates a queue provider map', function() {
        $kernel = new Job\Kernel();
        $kernel->config([
            'schedulers' => [
                ['queue' => 'jobs1'],
                ['queue' => 'jobs2', 'queue_provider' => 'redis'],
            ]
        ]);
        $kernel['krak.job.queue_provider'] = 'doctrine';
        assert($kernel->createQueueProviderMap() == [
            'jobs1' => 'doctrine',
            'jobs2' =>'redis',
        ]);
    });
});
