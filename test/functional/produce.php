<?php

use Krak\Job;

ini_set('date.timezone', 'America/Los_Angeles');

require_once __DIR__ . '/../../vendor/autoload.php';

class EchoJob implements Job\Job
{
    private $id;

    public function __construct($id) {
        echo "$id\n";
        $this->id = $id;
    }

    public function handle() {
        // return Job\complete(['id' => $this->id]);
    }
}

$kernel = require __DIR__ . '/kernel.php';
$dispatch = $kernel['dispatch'];

$num = intval($argv[2]);

$i = 0;
while ($i < $num) {
    $dispatch->wrap(new EchoJob($i))->onQueue($argv[1])->dispatch();
    $i += 1;
}
