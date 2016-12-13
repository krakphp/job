<?php

use Krak\Job,
    Skyzyx\Monolog\Formatter\JsonPrettyPrintFormatter;
ini_set('date.timezone', 'America/Los_Angeles');

require_once __DIR__ . '/../../vendor/autoload.php';

$app = new Symfony\Component\Console\Application();

$app->add(new Job\Console\SchedulerCommand(function() {
    return new Job\Scheduler(
        new Job\ProcessManager\SymfonyProcessManager(),
        Job\createQueueManager(new Predis\Client(['host' => 'redis'])),
        Krak\Mw\compose([
            Job\ScheduleLoop\queueScheduleLoop(),
            Job\ScheduleLoop\schedulerScheduleLoop(),
        ])
    );
}));
$app->add(new Job\Console\WorkerCommand(function() {
    return new Job\Worker(function($job) {
        if ($job->payload['data'] > 10) {
            return Job\Result::failed();
        } else {
            return Job\Result::complete();
        }
    });
}));

$app->run();
