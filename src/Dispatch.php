<?php

namespace Krak\Job;

interface Dispatch
{
    /** @return WrappedJobBuilder */
    public function wrap(Job $job);
    /** wraps and dispatches a job */
    public function dispatch(Job $job);
    /** Dispatches a wrapped job */
    public function dispatchWrappedJob(WrappedJob $job);
}
