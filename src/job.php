<?php

namespace Krak\Job;

/** empty interface for Jobs to extend */
interface Job {
    /** public function handle() */
}

/** returns a sucessful result */
function complete(array $payload = []) {
    return Result::complete($payload);
}

function failed(array $payload = []) {
    return Result::failed($payload);
}

function createKernel(...$args) {
    $qm = createQueueManager(...$args);
    return new Kernel\JobKernel($qm);
}

/** factory for creating queue managers */
function createQueueManager(...$args) {
    if ($args[0] instanceof \Predis\ClientInterface) {
        return new Queue\Redis\RedisQueueManager($args[0]);
    }

    throw new \InvalidArgumentException('Unable to determine queue type to create');
}


function registerConsole(\Symfony\Component\Console\Application $app, Kernel $kernel) {
    $app->addCommands([
        new Console\ConsumeCommand(),
        new Console\SchedulerCommand(),
        new Console\WorkerCommand(),
    ]);
    $app->getHelperSet()->set(new Console\JobHelper($kernel));
}