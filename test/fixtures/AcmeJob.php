<?php

namespace Krak\Job\TestFixtures;

use Krak\Job;

class AcmeJob implements Job\Job, Job\PipeWrappedJob
{
    public $id;

    public function __construct($id) {
        $this->id = $id;
    }

    public function pipe(Job\WrappedJob $wrapped) {
        return $wrapped->withName('acme')
            ->withQueue('custom_queue');
    }
}
