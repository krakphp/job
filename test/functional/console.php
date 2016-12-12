<?php

use Krak\Job,
    Skyzyx\Monolog\Formatter\JsonPrettyPrintFormatter;
ini_set('date.timezone', 'America/Los_Angeles');

require_once __DIR__ . '/../../vendor/autoload.php';

$app = new Symfony\Component\Console\Application();

$app->add(new Job\Console\SchedulerCommand(function() {
    $logger = new Monolog\Logger('Process Manager');
    $handler = new Monolog\Handler\StreamHandler('php://output');
    // $handler->setFormatter(new JsonPrettyPrintFormatter());
    $logger->pushHandler($handler);

    $manager = new Job\ProcessManager\SymfonyProcessManager();
    // $manager = new Job\ProcessManager\LoggingProcessManager($manager, $logger);

    return new Job\Scheduler(
        $manager,
        Job\createQueueManager(new Predis\Client(['host' => 'redis'])),
        Krak\Mw\compose([
            Job\ScheduleLoop\queueScheduleLoop(),
            Job\ScheduleLoop\schedulerScheduleLoop(),
        ]),
        $logger
    );
}));
$app->add(new Job\Console\WorkerCommand(function() {
    return new Job\Worker(function() {
        return Job\Result::complete();
    });
}));

$app->run();
