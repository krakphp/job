<?php

use Krak\Job\ProcessManager\MockProcessManager;

describe('MockProcessManager', function() {
    beforeEach(function() {
        $this->pm = new MockProcessManager();
    });
    describe('->launch', function() {
        it('launches a process and returns the pid', function() {
            $pid = $this->pm->launch(1, '');
            assert(is_int($pid));
        });
    });
    describe('->count', function() {
        it('returns the number of running procs', function() {
            $this->pm->launch(1, '');
            $this->pm->launch(1, '');
            assert(count($this->pm) == 2);
        });
    });
    describe('->reap', function() {
        it('decrements all procs and reaps all finished procs', function() {
            $this->pm->launch(1, '');
            $this->pm->launch(2, '');
            $this->pm->launch(2, '');
            $done1 = $this->pm->reap();
            $done2 = $this->pm->reap();
            assert(count($done1) == 1 && count($done2) == 2);
        });
    });
});
