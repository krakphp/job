<?php

namespace Krak\Job\Console;

use Psr\Log;

class ChainLogger extends Log\AbstractLogger
{
    private $loggers;

    public function __construct(array $loggers) {
        $this->loggers = $loggers;
    }

    public function log($level, $message, array $context = []) {
        foreach ($this->loggers as $logger) {
            $logger->log($level, $message, $context);
        }
    }
}
