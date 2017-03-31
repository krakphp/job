<?php

use Krak\Job;

$kernel = new Job\Kernel();
$kernel['Predis\ClientInterface'] = function() {
    return new Predis\Client(['host' => 'krak-job-redis']);
};
$kernel['Aws\Sqs\SqsClient'] = function() {
    return new Aws\Sqs\SqsClient([
        'version' => 'latest',
        'region' => 'us-west-1',
    ]);
};
$kernel['krak.job.queue_provider'] = 'sqs';
$kernel['krak.job.queue.sqs.receive_options'] = ['MaxNumberOfMessages' => 5];
$kernel[SplStack::class] = function() {
    $s = new SplStack();
    $s->push(1);
    $s->push(2);
    $s->push(2);
    $s->push(2);
    return $s;
};
$kernel->config([
    'queue' => 'jobs1',
    'sleep' => 2,
    'ttl' => 50,
]);

return $kernel;
