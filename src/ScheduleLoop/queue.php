<?php

namespace Krak\Job\ScheduleLoop;

use Krak\Job;
use Krak\Mw;
use iter;

function queueScheduleLoop($fail_job = null, $loop = null) {
    $loop = $loop ?: mw\group([
        sleepScheduleLoop(),
        killOnEmptyScheduleLoop(),
        queueJobDispatchScheduleLoop(),
        queueJobReapScheduleLoop($fail_job),
        statsLogScheduleLoop(),
        ttlScheduleLoop(),
        killFromCacheScheduleLoop(),
    ]);
    return function($params, $next) use ($loop) {
        if (!$params->has('queue')) {
            return $next($params);
        }

        return $loop($params, $next);
    };
}

/** This polls the queue and performs the dispatch */
function queueJobDispatchScheduleLoop() {
    return function($params, $next) {
        $max_jobs = $params->get('max_jobs', INF);
        $batch_size = $params->get('batch_size', 1);

        $cur_jobs = count($params->process_manager);
        if ($cur_jobs >= $max_jobs || $params->get('kill', false)) {
            return $next($params);
        }

        $queue = $params->queue();
        $batch = [];
        while ($job = $queue->dequeue()) {
            $batch[] = $job;

            if (count($batch) >= $batch_size) {
                _batchJobs($batch, $params);
                $batch = [];
            }

            $cur_jobs = count($params->process_manager);
            if ($cur_jobs >= $max_jobs) {
                $params->logger->debug('cur_jobs >= max_jobs');
                break;
            }
        }

        if (count($batch)) {
            _batchJobs($batch, $params);
        }

        return $next($params);
    };
}

function _batchJobs(array $jobs, $params) {
    foreach ($jobs as $job) {
        $params->logger->notice("Starting Job {name}", [
            'name' => $job->payload['name'],
        ]);
    }

    $params->process_manager->launch(
        $params->getWorkerCommand(),
        Job\serializeJobs($jobs),
        $jobs
    );
}

/** reaps all of the finished jobs. Allows for a max_retry configuration */
function queueJobReapScheduleLoop($fail_job) {
    return function($params, $next) use ($fail_job) {
        $queue = $params->queue();

        $finished = $params->process_manager->reap();

        foreach ($finished as $tup) {
            list($proc, $jobs) = $tup;
            if (!$proc->isSuccessful()) {
                $params->logger->error("Job {name} Process #{pid} encountered an error\n{output}", [
                    'name' => $job->payload['name'],
                    'pid' => $proc->getPid(),
                    'output' => $proc->getErrorOutput() ?: $proc->getOutput(),
                ]);
                foreach ($jobs as $job) {
                    $fail_job($job, $params);
                }
                continue;
            }

            $results = unserialize($proc->getOutput());
            if (!_assertResults($results)) {
                $params->logger->error("Worker #{pid} returned invalid output\n{output}", [
                    'pid' => $proc->getPid(),
                    'output' => $proc->getOutput(),
                ]);
                foreach ($jobs as $job) {
                    $fail_job($job, $params);
                }
                continue;
            }

            foreach (iter\zip($results, $jobs) as $tup) {
                list($result, $job) = $tup;

                $params->logger->notice("Job {name} finished with status: {status}\n{payload}", [
                    'name' => $job->payload['name'],
                    'status' => $result->status,
                    'payload' => json_encode($result->payload, JSON_PRETTY_PRINT),
                ]);

                if ($result->isFailed()) {
                    $fail_job($job, $params);
                } else {
                    $queue->complete($job);
                }
            }
        }

        return $next($params);
    };
}

function _assertResults($results) {
    return is_array($results) && iter\all(iter\fn\operator('instanceof', Job\Result::class), $results);
}
