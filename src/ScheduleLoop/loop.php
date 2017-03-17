<?php

namespace Krak\Job\ScheduleLoop;

function sleepScheduleLoop($sleep = 'sleep') {
    return function($params, $next) use ($sleep) {
        if (!$params->has('sleep')) {
            return $next($params);
        }

        $params->logger->debug('going to sleep for {sleep} seconds', [
            'sleep' => $params->get('sleep')
        ]);
        $sleep($params->get('sleep'));
        return true;
    };
}

function killOnEmptyScheduleLoop() {
    return function($params, $next) {
        $stop = $params->get('kill', false);
        if (!$stop) {
            return $next($params);
        }

        $params->logger->debug('Killing on empty');
        if (count($params->process_manager)) {
            return $next($params);
        } else {
            return false;
        }
    };
}

function _formatSeconds($seconds) {
    $dtF = new \DateTime('@0');
    $dtT = new \DateTime("@$seconds");
    return $dtF->diff($dtT)->format('%a days, %h hours, %i minutes and %s seconds');
}

function statsLogScheduleLoop() {
    $stats = [
        'time_start' => time(),
        'memory_start' => memory_get_usage(),
    ];
    return function($params, $next) use (&$stats) {
        $now = time();
        $log = <<<LOG
Stats
    Total Runtime: {total_runtime}
    Total Runtime in Seconds: {total_runtime_seconds}
    Total Memory Usage in MB: {total_memory_usage_mb}
    Peak Memory Usage in MB: {peak_memory_usage_mb}
    Number of Processes: {num_procs}
LOG;
        $params->logger->debug($log, [
            'total_runtime' => _formatSeconds($now - $stats['time_start']),
            'total_runtime_seconds' => $now - $stats['time_start'],
            'total_memory_usage_mb' => round((memory_get_usage() - $stats['memory_start']) / 1024 / 1024, 4),
            'peak_memory_usage_mb' => round(memory_get_peak_usage() / 1024 / 1024, 4),
            'num_procs' => count($params->process_manager),
        ]);

        return $next($params);
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
            $params->logger->debug('Ttl exceeded, sending kill', [
                'ts' => $ts,
                'ttl' => $params->get('ttl'),
                'then' => $then
            ]);
            $params->options['kill'] = true;
        }

        return $next($params);
    };
}
