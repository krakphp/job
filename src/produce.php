<?php

namespace Krak\Job;

interface Produce {
    public function __invoke(Job $job);
}

function queueProduce(Queue\QueueManager $manager) {
    return function($job) use ($manager) {
        $payload = $job->payload;

        if (!isset($payload['_queue'])) {
            throw new \RuntimeException('No queue name found for job: ' . $job->name);
        }

        $queue_name = $payload['_queue'];
        $queue = $manager->getQueue($queue_name);
        $queue->enqueue($job);
    };
}

/** timestamps the job */
function timestampProduce() {
    return function($job, $next) {
        $payload = $job->payload;
        $payload['_created_at'] = time();
        return $next($job->withPayload($payload));
    };
}

function onQueueProduce($queue, callable $mw) {
    return function(Job $job, $next) use ($queue, $mw) {
        $payload = $job->payload;
        if (!isset($payload['_queue']) || $payload['_queue'] != $queue) {
            return $next($job);
        }

        return $mw($job, $next);
    };
}

function onNameProduce($name, callable $mw) {
    return function(Job $job, $next) use ($name, $mw) {
        if ($job->name != $name) {
            return $next($job);
        }

        return $mw($job, $next);
    };
}

/** determine the queue name from the prefix by separator */
function queueNamePrefixProduce($sep = '.') {
    return function(Job $job, $next) use ($sep) {
        $parts = explode($sep, $job->name);
        if (count($parts) == 1) {
            // queue name could not be determined because there wasn't a prefix
            return $next($job);
        }

        $payload = $job->payload;
        $payload['_queue'] = $parts[0];
        return $next($job->withPayload($payload));
    };
}

function queueNameMapProduce(array $queue_names) {
    return function($job, $next) use ($queue_names) {
        $payload = $job->payload;
        if (isset($payload['_queue'])) {
            return $next($job);
        }
        if (isset($queue_names[$job->name])) {
            $payload['_queue'] = $queue_names[$job->name];
        }

        return $next($job->withPayload($payload));
    };
}
