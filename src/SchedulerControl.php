<?php

namespace Krak\Job;

use Psr\SimpleCache\CacheInterface;
use Psr\Log;
use Symfony\Component\Process;

/** Manages a running scheduler via the cache */
class SchedulerControl
{
    private $cache;

    public function __construct(CacheInterface $cache) {
        $this->cache = $cache;
    }

    public function restartScheduler($name, Log\LoggerInterface $logger = null) {
        $logger = $logger ?: new Log\NullLogger();
        $cache = SimpleCache\PrefixCache::wrapInstanceName($name, $this->cache);

        $this->stopScheduler($name, $logger);

        $options = $cache->get('scheduler_options');
        if (!$options) {
            $logger->notice("Cannot restart scheduler that never ran.");
            return;
        }

        $logger->info("Starting scheduler.");
        $logger->debug('Consume command: ' . $options['_consume_cmd']);

        $options['_root'] = true;

        $proc = new Process\Process($options['_consume_cmd']);
        $proc->setInput(json_encode($options));
        $proc->start();

        do {
            $logger->info("Waiting for scheduler to start up...");
            sleep(2);
            $stats = $cache->get('scheduler_stats');
        } while (!$stats['running']);

        $logger->info("Scheduler is running.");
    }

    public function stopScheduler($name, Log\LoggerInterface $logger = null) {
        $logger = $logger ?: new Log\NullLogger();
        $cache = SimpleCache\PrefixCache::wrapInstanceName($name, $this->cache);

        $stats = $cache->get('scheduler_stats');
        if (!$stats || !$stats['running']) {
            $logger->notice('Scheduler is not running.');
            return;
        }

        $logger->info('Killing scheduler');
        $cache->set('kill', true);

        do {
            $logger->info("Waiting for scheduler to stop...");
            sleep(2);
            $stats = $cache->get('scheduler_stats');
        } while ($stats['running']);

        $duration = ScheduleLoop\_formatSeconds($stats['end'] - $stats['start']);
        $logger->info("Scheduler stopped after " . $duration . '.');
    }

    public function logStatus($name, Log\LoggerInterface $logger = null) {
        $logger = $logger ?: new Log\NullLogger();
        $cache = SimpleCache\PrefixCache::wrapInstanceName($name, $this->cache);

        $stats = $cache->get('scheduler_stats');
        if (!$stats || !$stats['running']) {
            $logger->info('Scheduler is not running.');
            return;
        }

        $duration = ScheduleLoop\_formatSeconds(time() - $stats['start']);
        $logger->info("Scheduler has been running for " . $duration . '.');
    }

    public function resetSchedulerCache($name, Log\LoggerInterface $logger = null) {
        $logger = $logger ?: new Log\NullLogger();
        $cache = SimpleCache\PrefixCache::wrapInstanceName($name, $this->cache);

        $logger->info("Resetting scheduler cache.");

        $cache->deleteMultiple([
            'scheduler_stats',
            'scheduler_options',
            'kill'
        ]);
    }
}
