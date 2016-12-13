<?php

namespace Krak\Job\ScheduleLoop;

use Krak\Job\Result,
    Krak\Mw;

function queueScheduleLoop($loop = null) {
    $loop = $loop ?: mw\group([
        sleepScheduleLoop(),
        queueJobDispatchScheduleLoop(),
        queueJobReapScheduleLoop(),
        statsLogScheduleLoop(),
        ttlScheduleLoop(),
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

        $cur_jobs = count($params->process_manager);
        if ($cur_jobs >= $max_jobs) {
            return $next($params);
        }

        $queue = $params->queue();
        while ($job = $queue->dequeue()) {
            $params->logger->info("launching job {name}", [
                'name' => $job->name,
            ]);
            $params->process_manager->launch(
                $params->get('worker_cmd'),
                serialize($job),
                $job
            );

            $cur_jobs = count($params->process_manager);
            if ($cur_jobs >= $max_jobs) {
                $params->logger->debug('cur_jobs >= max_jobs');
                break;
            }
        }

        return $next($params);
    };
}

/** reaps all of the finished jobs. Allows for a max_retry configuration */
function queueJobReapScheduleLoop($fail_job = null) {
    $fail_job = $fail_job ?: _failJob();
    return function($params, $next) use ($fail_job) {
        $queue = $params->queue();

        $finished = $params->process_manager->reap();

        foreach ($finished as $tup) {
            list($proc, $job) = $tup;
            if (!$proc->isSuccessful()) {
                $params->logger->error("Job {name} Process #{pid} encountered an error\n{output}", [
                    'name' => $job->name,
                    'pid' => $proc->getPid(),
                    'output' => $proc->getErrorOutput(),
                ]);

                $fail_job($params, $job);
            } else {
                $res = unserialize($proc->getOutput());
                if (!$res || !$res instanceof Result) {
                    $params->logger->error("Job {name} Worker #{pid} returned invalid output\n{output}", [
                        'name' => $job->name,
                        'pid' => $proc->getPid(),
                        'output' => $proc->getOutput(),
                    ]);
                    $fail_job($params, $job);
                    continue;
                }
                $params->logger->info("Job {name} finished with status: {status}\n{payload}", [
                    'name' => $job->name,
                    'status' => $res->status,
                    'payload' => json_encode($res->payload, JSON_PRETTY_PRINT),
                ]);

                if ($res->isFailed()) {
                    $fail_job($params, $job);
                } else {
                    $queue->complete($job);
                }
            }
        }

        return $next($params);
    };
}
