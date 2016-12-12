<?php

namespace Krak\Job\ScheduleLoop;

function sleepScheduleLoop($sleep = 'sleep') {
    return function($params, $next) use ($sleep) {
        if (!$params->has('sleep')) {
            return $next($params);
        }

        $sleep($params->get('sleep'));
        return true;
    };
}

function ttlScheduleLoop() {
    $ts = time();
    return function($params, $next) use ($ts) {
        if (!$params->has('ttl')) {
            return $next($params);
        }

        $now = time();
        $then = $params->get('ttl') + $ts;

        // if we've exceeded the ttl, then kill the loop */
        if ($now >= $then) {
            $params->logger->debug('Ttl exceeded', [
                'ts' => $ts,
                'ttl' => $params->get('ttl'),
                'then' => $then
            ]);
            return false;
        }

        return $next($params);
    };
}

function _failJob() {
    return function($params, $job) {
        $max_retry = $params->get('max_retry', 0);
        $queue = $params->queue();

        $retry = isset($job->payload['_retry'])
            ? $job->payload['_retry']
            : 0;

        if ($retry >= $max_retry) {
            $params->logger->debug('Retry exceeded, failing job', [
                'job' => $job->name,
                'payload' => $job->payload,
                'retry' => $retry,
                'max_retry' => $max_retry,
            ]);
            $queue->fail($job);
        } else {
            $params->logger->debug('Retrying Job', [
                'job' => $job->name,
                'payload' => $job->payload,
                'retry' => $retry,
                'max_retry' => $max_retry,
            ]);
            $job->payload['_retry'] = $retry + 1;
            $queue->enqueue($job);
        }
    };
}
