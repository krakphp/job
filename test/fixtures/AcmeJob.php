<?php

namespace Krak\Job\TestFixtures;

use Krak\Job;

class AcmeJob implements Job\Job
{
    public $id;

    public function __construct($id) {
        $this->id = $id;
    }
}
