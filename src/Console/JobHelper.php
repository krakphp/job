<?php

namespace Krak\Job\Console;

use Symfony\Component\Console\Helper\Helper,
    Krak\Job;

class JobHelper extends Helper
{
    private $kernel;

    public function __construct(Job\Kernel $kernel) {
        $this->kernel = $kernel;
    }

    public function getKernel() {
        return $this->kernel;
    }

    public function getName() {
        return 'krak_job';
    }
}
