<?php

use Krak\Job;

ini_set('date.timezone', 'America/Los_Angeles');

require_once __DIR__ . '/../../vendor/autoload.php';

class EchoJob implements Job\Job
{
    private $id;
    public function __construct($id) {
        $this->id = $id;
    }

    public function handle(SplStack $stack) {
        return Job\complete(['id' => $this->id, 'count' => $stack->count()]);
    }
}

$app = new Symfony\Component\Console\Application();
Job\registerConsole($app, require __DIR__ . '/kernel.php');

$app->run();
