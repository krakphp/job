<?php

namespace Krak\Job;

/** Core of the Job Framework. This is the main entity that glues everything together */
interface Kernel
{
    /** returns a queue manager instance */
    public function getQueueManager();
    /** create a dispatcher */
    public function createDispatch();
    /** create a scheduler */
    public function createScheduler();
    /** create a worker */
    public function createWorker();
    /** configure the producer stack */
    public function producer(\Closure $predicate);
    /** configure the consumer stack */
    public function consumer(\Closure $predicate);
    /** configure the schedule loop */
    public function scheduleLoop(\Closure $predicate);
    /** configure the process manager */
    public function processManager(\Closure $predicate);
    /** configure the auto args context */
    public function autoArgsContext(\Closure $predicate);
}
