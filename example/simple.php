<?php

class AlertJob implements Krak\Job\Job
{
    private $id;
    public function __construct($id) {
        $this->id = $id;
    }

    public function handle() {}
}

$kernel = Krak\Job\createKernel(new Predis\Client());
$dispatch = $kernel->createDispatch();

Krak\Job\registerConsole($app, $kernel);
