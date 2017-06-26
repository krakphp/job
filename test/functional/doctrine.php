<?php

require_once __DIR__ . '/../../vendor/autoload.php';

$kernel = require __DIR__ . '/kernel.php';
$conn = $kernel['Doctrine\DBAL\Connection'];
$migration = $kernel['Krak\Job\Queue\Doctrine\JobMigration'];

if ($argv[1] == 'up') {
    $migration->migrateUp($conn);
} else {
    $migration->migrateDown($conn);
}
