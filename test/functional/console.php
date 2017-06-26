<?php

use Krak\Job;

ini_set('date.timezone', 'America/Los_Angeles');

require_once __DIR__ . '/../../vendor/autoload.php';

$app = new Symfony\Component\Console\Application();
Job\registerConsole($app, require __DIR__ . '/kernel.php');

$app->run();
