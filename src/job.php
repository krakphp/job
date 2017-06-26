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

/** factory for creating queue managers */
function createQueueManager(...$args) {
    if ($args[0] instanceof \Predis\ClientInterface) {
        return new Queue\Redis\RedisQueueManager($args[0]);
    }
    if ($args[0] instanceof \Aws\Sqs\SqsClient) {
        return new Queue\Sqs\SqsQueueManager(
            $args[0],
            isset($args[1]) ? $args[1] : [],
            isset($args[2]) ? $args[2] : []
        );
    }

    throw new \InvalidArgumentException('Unable to determine queue type to create');
}

function registerConsole(\Symfony\Component\Console\Application $app, Kernel $kernel) {
    $app->addCommands([
        new Console\ConsumeCommand(),
        new Console\SchedulerCommand(),
        new Console\WorkerCommand(),
    ]);
    if ($kernel->isCacheEnabled()) {
        $app->addCommands([
            new Console\RestartCommand(),
            new Console\StopCommand(),
            new Console\ResetCommand(),
            new Console\StatusCommand(),
        ]);
    }
    $app->getHelperSet()->set(new Console\JobHelper($kernel));
}
