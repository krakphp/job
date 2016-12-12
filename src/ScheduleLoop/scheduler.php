<?php

namespace Krak\Job\ScheduleLoop;

use Krak\Mw;

function schedulerScheduleLoop($loop = null) {
    $loop = $loop ?: mw\group([
        sleepScheduleLoop(),
        schedulerDispatchScheduleLoop(),
        schedulerReapScheduleLoop(),
        ttlScheduleLoop(),
    ]);
    return function($params, $next) use ($loop) {
        if (!$params->has('schedulers')) {
            return $next($params);
        }

        return $loop($params, $next);
    };
}

/** This polls the queue and performs the dispatch */
function schedulerDispatchScheduleLoop() {
    $has_scheduled = false;
    return function($params, $next) use (&$has_scheduled) {
        if ($has_scheduled) {
            return $next($params);
        }

        $schedulers = $params->get('schedulers');
        foreach ($schedulers as $options) {
            // merge the current options with the child options
            $popts = $params->options;
            unset($popts['schedulers']);
            $options = $options + $popts;

            $params->process_manager->launch(
                $params->get('scheduler_cmd'),
                json_encode($options),
                $options
            );
        }

        $has_scheduled = true;

        return $next($params);
    };
}

/** reaps all of the finished jobs. Allows for a max_retry configuration */
function schedulerReapScheduleLoop() {
    return function($params, $next) use ($fail_job) {
        $finished = $params->process_manager->reap();

        foreach ($finished as $tup) {
            list($success, $output) = $tup;
            if (!$success) {
                $params->logger->error('Scheduler Process encountered an error', [
                    'error' => $output,
                ]);
            } else {
                $params->logger->info('Scheduler Process Finished', [
                    'output' => $output
                ]);
            }
        }

        if ($finished && count($params->process_manager) == 0) {
            $params->logger->info('No more schedulers, exiting');
            return false;
        }

        return $next($params);
    };
}
