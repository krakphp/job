<?php

namespace Krak\Job;

abstract class AbstractJob implements Job, PipeWrappedJob
{
    public function handle() {

    }

    public function pipe(WrappedJob $wrapped) {
        return $wrapped;
    }
}
