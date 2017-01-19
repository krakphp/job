<?php

namespace Krak\Job;

interface Dispatch
{
    /** @return WrappedJobBuilder */
    public function wrap(Job $job);
    /** Dispatches a wrapped job */
    public function dispatch(WrappedJob $job);
}
