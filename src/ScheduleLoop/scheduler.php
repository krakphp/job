<?php

namespace Krak\Job\ScheduleLoop;

use Krak\Mw;

function schedulerScheduleLoop($loop = null) {
    $loop = $loop ?: mw\group([
        sleepScheduleLoop(),
        schedulerDispatchScheduleLoop(),
        schedulerReapScheduleLoop(),
        schedulerLogScheduleLoop(),
        statsLogScheduleLoop(),
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
            unset($popts['name']);
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

function schedulerLogScheduleLoop() {
    return function($params, $next) {
        $procs = $params->process_manager->getProcs();

        foreach ($procs as $tup) {
            list($proc, $options) = $tup;

            if ($proc->getOutput()) {
                $params->logger->info("stdout for #{pid}\n" . $proc->getOutput(), [
                    'pid' => $proc->getPid(),
                ]);
                $proc->clearOutput();
            }
            if ($proc->getErrorOutput()) {
                $params->logger->error("stderr for #{pid}\n" . $proc->getErrorOutput(), [
                    'pid' => $proc->getPid(),
                ]);
                $proc->clearErrorOutput();
            }
        }

        return $next($params);
    };
}

/** reaps all of the finished jobs. Allows for a max_retry configuration */
function schedulerReapScheduleLoop() {
    return function($params, $next) {
        $finished = $params->process_manager->reap();

        foreach ($finished as $tup) {
            list($proc) = $tup;
            if (!$proc->isSuccessful()) {
                $params->logger->error('Scheduler Process encountered an error');
            } else {
                $params->logger->info('Scheduler Process Finished');
            }
        }

        if ($finished && count($params->process_manager) == 0) {
            $params->logger->info('No more schedulers, exiting');
            return false;
        }

        return $next($params);
    };
}
