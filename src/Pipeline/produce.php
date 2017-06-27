<?php

namespace Krak\Job\Pipeline;

use Krak\Job;

function queueProduce(Job\Queue\QueueManagerResolver $resolver, $queue_provider_map) {
    return function($job) use ($resolver, $queue_provider_map) {
        if (!$job->getQueue()) {
            throw new \RuntimeException('No queue name found for job: ' . $job->getName());
        }

        $queue_provider = $queue_provider_map[$job->getQueue()];
        $queue = $resolver->resolveQueueManager($queue_provider)
            ->getQueue($job->getQueue());

        return $queue->enqueue($job);
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
        if ($wrapped->getName()) {
            return $next($wrapped);
        }

        return $next($wrapped->withName(get_class($wrapped->job)));
    };
}

/** map a job's class name to name field in the wrapped job payload */
function classToNameProduce($prefix = '', $sep = '.') {
    return function(Job\WrappedJob $wrapped, $next) use ($prefix, $sep) {
        if ($wrapped->getName()) {
            return $next($wrapped);
        }

        $cls = get_class($wrapped->job);
        if (strpos($cls, $prefix) === 0) {
            $cls = substr($cls, strlen($prefix));
        }

        $name = strtolower(str_replace('\\', $sep, $cls));
        return $next($wrapped->withName($name));
    };
}

/** defaults the queue name to the given name if one does not exist */
function defaultQueueNameProduce($queue) {
    return function(Job\WrappedJob $wrapped, $next) use ($queue) {
        if ($wrapped->getQueue()) {
            return $next($wrapped);
        }

        return $next($wrapped->withQueue($queue));
    };
}

/** determine the queue name from the prefix by separator */
function queueNamePrefixProduce($sep = '.') {
    return function(Job\WrappedJob $wrapped, $next) use ($sep) {
        if ($wrapped->getQueue() || !$wrapped->getName()) {
            return $next($wrapped);
        }

        $parts = explode($sep, $wrapped->getName());
        if (count($parts) == 1) {
            // queue name could not be determined because there wasn't a prefix
            return $next($wrapped);
        }

        return $next($wrapped->withQueue($parts[0]));
    };
}
