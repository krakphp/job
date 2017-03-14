<?php

class AlertJob implements Krak\Job\Job
{
    private $id;
    public function __construct($id) {
        $this->id = $id;
    }

    public function handle() {}
}

$kernel = new Krak\Job\Kernel();
$kernel->config([
    'queue' => 'jobs',
    'sleep' => 10,
]);
$kernel->queueManager(function() {
    return Krak\Job\createQueueManager(new Predis\Client());
});

if ($argv[1] == 'dispatch') {
    $kernel->dispatch(new AlertJob(1));
} else {
    $app = new Symfony\Component\Console\Application();
    Krak\Job\registerConsole($app, $kernel);
    $app->run();
}
