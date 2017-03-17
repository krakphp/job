<?php

namespace Krak\Job\Pipeline;

use Krak\Job;

function queueProduce(Job\Queue\QueueManager $manager) {
    return function($job) use ($manager) {
        $payload = $job->payload;

        if (!isset($payload['queue'])) {
            throw new \RuntimeException('No queue name found for job: ' . $job->payload['name']);
        }

        $queue_name = $payload['queue'];
        $queue = $manager->getQueue($queue_name);
        $queue->enqueue($job);
    };
}

/** timestamps the job */
function timestampProduce() {
    return function($job, $next) {
        return $next($job->withAddedPayload(['created_at' => time()]));
    };
}

function autoQueueNameProduce($prefix, $sep = '.') {
    return mw\group([
        queueNamePrefixProduce($sep),
        classToNameProduce($prefix, $sep)
    ]);
}

function classNameProduce() {
    return function(Job\WrappedJob $wrapped, $next) {
        if (isset($wrapped->payload['name'])) {
            return $next($wrapped);
        }

        return $next($wrapped->withAddedPayload(['name' => get_class($wrapped->job)]));
    };
}

/** map a job's class name to name field in the wrapped job payload */
function classToNameProduce($prefix = '', $sep = '.') {
    return function(Job\WrappedJob $wrapped, $next) use ($prefix, $sep) {
        if (isset($wrapped->payload['name'])) {
            return $next($wrapped);
        }

        $cls = get_class($wrapped->job);
        if (strpos($cls, $prefix) === 0) {
            $cls = substr($cls, strlen($prefix));
        }

        $name = strtolower(str_replace('\\', $sep, $cls));
        return $next($wrapped->withAddedPayload(['name' => $name]));
    };
}

/** defaults the queue name to the given name if one does not exist */
function defaultQueueNameProduce($queue) {
    return function(Job\WrappedJob $wrapped, $next) use ($queue) {
        if (isset($wrapped->payload['queue'])) {
            return $next($wrapped);
        }

        return $next($wrapped->withAddedPayload(['queue' => $queue]));
    };
}

/** determine the queue name from the prefix by separator */
function queueNamePrefixProduce($sep = '.') {
    return function(Job\WrappedJob $wrapped, $next) use ($sep) {
        if (isset($wrapped->payload['queue']) || !isset($wrapped->payload['name'])) {
            return $next($wrapped);
        }

        $parts = explode($sep, $wrapped->payload['name']);
        if (count($parts) == 1) {
            // queue name could not be determined because there wasn't a prefix
            return $next($wrapped);
        }

        return $next($wrapped->withAddedPayload(['queue' => $parts[0]]));
    };
}
