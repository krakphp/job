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
