<?php

use Krak\Job;

ini_set('date.timezone', 'America/Los_Angeles');

require_once __DIR__ . '/../../vendor/autoload.php';

$manager = Job\createQueueManager(new Predis\Client(['host' => 'redis']));

$produce = Krak\Mw\compose([
    Job\queueProduce($manager)
]);

$produce(new Job\Job('test-job', [
    '_queue' => 'jobs',
    'data' => 1,
]));
