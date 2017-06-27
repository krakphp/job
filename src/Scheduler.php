<?php

namespace Krak\Job;

use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;
use iter;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process;

use function Krak\Mw\compose;

class Scheduler
{
    private $process_manager;
    private $queue_manager_resolver;
    private $loop;
    private $cache;

    public function __construct(ProcessManager\ProcessManager $process_manager, Queue\QueueManagerResolver $queue_manager_resolver, $loop, CacheInterface $cache = null) {
        $this->process_manager = $process_manager;
        $this->queue_manager_resolver = $queue_manager_resolver;
        $this->loop = $loop;
        $this->cache = $cache;
    }

    public function run(OutputInterface $output, LoggerInterface $logger, array $options) {
        $i = 0;

        $params = new ScheduleLoop\ScheduleLoopParams();
        $params->process_manager = $this->process_manager;
        $params->queue_manager = new Queue\CachedQueueManager(
            $this->queue_manager_resolver->resolveQueueManager($options['queue_provider'])
        );
        $params->logger = $logger;
        $params->output = $output;
        $params->options = $options;
        $params->iteration_count = $i;

        $params->cache = SimpleCache\PrefixCache::wrapInstanceName(
            $params->getInstanceName(),
            $this->cache
        );

        $loop = $this->loop;

        $is_root = $params->get('_root', false);
        if ($is_root) {
            unset($params->options['_root']);
            if ($params->cache) {
                $this->initializeCache($params);
            }
        }

        while($loop($params)) {
            $i += 1;
            $params->iteration_count = $i;
        }

        if ($is_root) {
            if ($params->cache) {
                $stats = $params->cache->get('scheduler_stats');
                $stats['running'] = false;
                $stats['end'] = time();
                $params->cache->set('scheduler_stats', $stats);
            }
        }
    }

    private function initializeCache($params) {
        $params->cache->set('scheduler_stats', [
            'running' => true,
            'start' => time(),
        ]);
        $params->cache->set('scheduler_options', $params->options);
        $params->cache->set('kill', false);
    }
}
