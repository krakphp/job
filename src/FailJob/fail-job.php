<?php

namespace Krak\Job\FailJob;

function completeFailJob() {
    return function($job, $params, $next) {
        $queue = $params->queue();
        $params->logger->debug('Failing Job {name} and marking as complete', [
            'name' => $job->getName()
        ]);
        $queue->complete($job);
    };
}

function retryFailJob() {
    return function($job, $params, $next) {
        $max_retry = $params->get('max_retry');
        if ($max_retry === null) {
            return $next($job, $params);
        }

        $queue = $params->queue();

        $retry = isset($job->payload['_retry'])
            ? $job->payload['_retry']
            : 0;

        if ($retry >= $max_retry) {
            $params->logger->debug('Retry exceeded, failing job {job}', [
                'job' => $job->getName(),
                'payload' => $job->payload,
                'retry' => $retry,
                'max_retry' => $max_retry,
            ]);
            return $next($job, $params);
        } else {
            $params->logger->debug('Retrying Job {job}', [
                'job' => $job->getName(),
                'payload' => $job->payload,
                'retry' => $retry,
                'max_retry' => $max_retry,
            ]);
            $job->payload['_retry'] = $retry + 1;
            $queue->enqueue($job);
        }
    };
}
