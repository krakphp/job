<?php

namespace Krak\Job\Kernel;

use Krak\Job;

/** Core of the Job Framework. This is the main entity that glues everything together */
abstract class AbstractKernelDecorator implements Job\Kernel
{
    protected $kernel;

    public function __construct(Job\Kernel $kernel) {
        $this->kernel = $kernel;
    }

    /** returns a queue manager instance */
    public function getQueueManager() {
        return $this->kernel->getQueueManager();
    }

    /** create a dispatcher */
    public function createDispatch() {
        return $this->kernel->createDispatch();
    }
    /** create a scheduler */
    public function createScheduler() {
        return $this->kernel->createScheduler();
    }
    /** create a worker */
    public function createWorker() {
        return $this->kernel->createWorker();
    }
    /** configure the producer stack */
    public function producer(\Closure $predicate) {
        return $this->kernel->producer($predicate);
    }
    /** configure the consumer stack */
    public function consumer(\Closure $predicate) {
        return $this->kernel->consumer($predicate);
    }
    /** configure the schedule loop */
    public function scheduleLoop(\Closure $predicate) {
        return $this->kernel->scheduleLoop($predicate);
    }
    /** configure the process manager */
    public function processManager(\Closure $predicate) {
        return $this->kernel->processManager($predicate);
    }
    /** configure the auto args context */
    public function autoArgsContext(\Closure $predicate) {
        return $this->kernel->autoArgsContext($predicate);
    }
}
