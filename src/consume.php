<?php

namespace Krak\Job;

interface Consumer {
    /** @return Result */
    public function __invoke(Job $job);
}

function catchExceptionConsumer() {
    return function(Job $job, $next) {
        try {
            return $next($job);
        } catch (\Exception $e) {
            return Result::failed(['exception' => $e->getMessage()]);
        }
    };
}

function onQueueConsumer($queue, callable $mw) {
    return function(Job $job, $next) use ($queue, $mw) {
        $payload = $job->payload;
        if (!isset($payload['_queue']) || $payload['_queue'] != $queue) {
            return $next($job);
        }

        return $mw($job, $next);
    };
}

function onNameConsumer($name, callable $mw) {
    return function(Job $job, $next) use ($name, $mw) {
        if ($job->name != $name) {
            return $next($job);
        }

        return $mw($job, $next);
    };
}
