<?php

namespace Krak\Job;

interface PipeWrappedJob
{
    public function pipe(WrappedJob $wrapped);
}
