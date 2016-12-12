<?php

use Krak\Job;

require_once __DIR__ . '/../../vendor/autoload.php';

$logger = new Monolog\Logger('Process Manager');
$logger->pushHandler(new Monolog\Handler\StreamHandler('php://output'));

$manager = new Job\ProcessManager\SymfonyProcessManager();
$manager = new Job\ProcessManager\LoggingProcessManager($manager, $logger);

$manager->launch('sleep 1', '', 'a');
$manager->launch('sleep 2', '', 'b');
$manager->launch('sleep 2 && bad-command-name', '', 'c');
$manager->launch('sleep 3', '', 'd');

while (count($manager)) {
    $manager->reap();
    sleep(1);
}
