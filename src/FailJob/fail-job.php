<?php

namespace Krak\Job\FailJob;

function completeFailJob() {
    return function($job, $params, $next) {
        $queue = $params->queue();
        $params->logger->debug('Fail Job: marking as complete');
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
            $params->logger->debug('Retry exceeded, failing job', [
                'job' => $job->name,
                'payload' => $job->payload,
                'retry' => $retry,
                'max_retry' => $max_retry,
            ]);
            return $next($job, $params);
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
