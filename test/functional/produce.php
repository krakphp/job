<?php

use Krak\Job;

ini_set('date.timezone', 'America/Los_Angeles');

require_once __DIR__ . '/../../vendor/autoload.php';

$kernel = require __DIR__ . '/kernel.php';
$dispatch = $kernel['dispatch'];

$num = intval($argv[2]);
$i = 0;
while ($i < $num) {
    echo "Dispatching Job\n";
    $dispatch->wrap(new Job\TestFixtures\EchoJob($i))->onQueue($argv[1])->dispatch();
    $i += 1;
}
