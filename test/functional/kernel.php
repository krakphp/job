<?php

use Krak\Job;

$kernel = new Job\Kernel();
$kernel->queueManager(function($qm, $c) {
    return Job\createQueueManager(new Predis\Client(['host' => 'krak-job-redis']));
});
$kernel[SplStack::class] = function() {
    $s = new SplStack();
    $s->push(1);
    $s->push(2);
    $s->push(2);
    $s->push(2);
    return $s;
};
$kernel->config([
    // 'name' => "Master Scheduler",
    // 'sleep' => 5,
    // 'schedulers' => [
        // ['queue' => 'jobs', 'max_jobs' => 10],
    'queue' => 'jobs1',
    'sleep' => 2,
    'ttl' => 50,
    // ]
]);

return $kernel;
