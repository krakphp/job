<?php

namespace Krak\Job\Kernel;

use Krak\Job,
    Krak\Mw,
    Krak\AutoArgs;

class JobKernel implements Job\Kernel
{
    protected $queue_manager;
    protected $process_manager_predicate;
    protected $producer_predicate;
    protected $consumer_predicate;
    protected $schedule_loop_predicate;
    protected $auto_args_context_predicate;

    public function __construct(Job\Queue\QueueManager $queue_manager) {
        $this->queue_manager = $queue_manager;
    }

    public function getQueueManager() {
        return $this->queue_manager;
    }

    public function createDispatch() {
        return new Job\Dispatch\ProducerDispatch(
            self::createProducerStack($this->queue_manager, $this->consumer_predicate)->compose()
        );
    }
    public function createScheduler() {
        return new Job\Scheduler(
            self::createProcessManager($this->process_manager_predicate),
            $this->queue_manager,
            self::createScheduleLoopStack($this->schedule_loop_predicate)->compose()
        );
    }
    public function createWorker() {
        return new Job\Worker(
            self::createConsumerStack(
                self::createAutoArgsContext($this, $this->auto_args_context_predicate),
                $this->consumer_predicate
            )->compose()
        );
    }

    public function producer(\Closure $predicate) {
        $this->producer_predicate = $this->wrap(
            $this->producer_predicate,
            $predicate
        );
    }
    public function consumer(\Closure $predicate) {
        $this->consumer_predicate = $this->wrap(
            $this->consumer_predicate,
            $predicate
        );
    }
    public function processManager(\Closure $predicate) {
        $this->process_manager_predicate = $predicate;
        $this->process_manager_predicate = $this->wrap(
            $this->process_manager_predicate,
            $predicate
        );
    }
    public function scheduleLoop(\Closure $predicate) {
        $this->schedule_loop_predicate = $predicate;
        $this->schedule_loop_predicate = $this->wrap(
            $this->schedule_loop_predicate,
            $predicate
        );
    }
    public function autoArgsContext(\Closure $predicate) {
        $this->auto_args_context_predicate = $predicate;
        $this->auto_args_context_predicate = $this->wrap(
            $this->auto_args_context_predicate,
            $predicate
        );
    }

    private function wrap($cur_pred, $predicate) {
        return function($arg) use ($cur_pred, $predicate) {
            $arg = $cur_pred ? $cur_pred($arg) : $arg;
            return $predicate($arg);
        };
    }

    public static function createProcessManager($predicate = null) {
        $pm = new Job\ProcessManager\SymfonyProcessManager();
        if ($predicate) {
            $pm = $predicate($pm);
        }
        return $pm;
    }
    public static function createScheduleLoopStack($predicate = null) {
        $stack = mw\stack('Job Schedule Loop')
            ->push(Job\ScheduleLoop\queueScheduleLoop(), 0, 'queue')
            ->push(Job\ScheduleLoop\schedulerScheduleLoop(), 0, 'scheduler');

        if ($predicate) {
            $stack = $predicate($stack);
        }

        return $stack;
    }
    public static function createProducerStack(Job\Queue\QueueManager $qm, $predicate = null) {
        $stack = mw\stack('Job Producer')
            ->push(Job\Pipeline\queueProduce($qm), -1, 'queue')
            ->push(Job\Pipeline\timestampProduce(), 0, 'timestamp')
            ->push(Job\Pipeline\classNameProduce(), 0, 'name');

        if ($predicate) {
            $stack = $predicate($stack);
        }

        return $stack;
    }
    public static function createConsumerStack($auto_args_context, $predicate = null) {
        $stack = mw\stack('Job Consumer')
            ->push(Job\Pipeline\invokeJobConsume(
                new AutoArgs\AutoArgs(),
                $auto_args_context
            ), -1, 'invokeJob')
            ->push(Job\Pipeline\catchExceptionConsume(), 1, 'catchException');

        if ($predicate) {
            $stack = $predicate($stack);
        }

        return $stack;
    }
    public static function createAutoArgsContext(Job\Kernel $kernel, $predicate = null) {
        $ctx = [
            'objects' => [$kernel]
        ];
        if ($predicate) {
            $ctx = $predicate($ctx);
        }
        return $ctx;
    }
}
