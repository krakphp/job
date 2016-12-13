<?php

namespace Krak\Job\Console;

use Psr\Log;

class PrefixLogger extends Log\AbstractLogger
{
    private $logger;
    private $prefix;

    public function __construct(Log\LoggerInterface $logger, $prefix) {
        $this->logger = $logger;
        $this->prefix = $prefix;
    }

    public function log($level, $message, array $context = []) {
        return $this->logger->log($level, $this->prefix . $message, $context);
    }
}
