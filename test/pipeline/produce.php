<?php

use Krak\Job;
use Krak\Mw;

describe('#pipeJobProduce', function() {
    it('pipes a wrapped job through the job pipe method', function() {
        $produce = Job\Pipeline\pipeJobProduce();
        $handler = mw\compose([
            mw\identity(),
            $produce,
        ]);

        $wrapped = new Job\WrappedJob(new Job\TestFixtures\AcmeJob(1));
        $wrapped = $handler($wrapped);
        assert($wrapped->getQueue() == 'custom_queue' && $wrapped->getName() == 'acme');
    });
    it('does nothing if job is not instance of PipeWrappedJob', function() {
        $produce = Job\Pipeline\pipeJobProduce();
        $handler = mw\compose([
            mw\identity(),
            $produce,
        ]);

        $wrapped = new Job\WrappedJob(new class() implements Job\Job {});
        $new_wrapped = $handler($wrapped);
        assert($wrapped === $new_wrapped);
    });
});
