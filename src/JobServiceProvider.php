<?php

namespace Krak\Job;

use Krak\Cargo;
use Krak\Mw;
use Krak\AutoArgs;
use Psr\SimpleCache\CacheInterface;
use Psr\Log;

class JobServiceProvider implements Cargo\ServiceProvider
{
    public function register(Cargo\Container $c) {
        $c[Dispatch::class] = function($c) {
            $produce = $c['krak.job.pipeline.produce'];
            return new Dispatch\ProducerDispatch(Mw\compose([$produce]));
        };
        $c[Queue\Doctrine\JobRepository::class] = function($c) {
            return new Queue\Doctrine\JobRepository(
                $c['Doctrine\DBAL\Connection'],
                $c['krak.job.queue.doctrine.table_name']
            );
        };
        $c[Queue\Doctrine\JobMigration::class] = function($c) {
            return new Queue\Doctrine\JobMigration(
                $c['krak.job.queue.doctrine.table_name']
            );
        };
        $c[Queue\Doctrine\DoctrineQueueManager::class] = function($c) {
            return new Queue\Doctrine\DoctrineQueueManager(
                $c[Queue\Doctrine\JobRepository::class]
            );
        };
        $c[Queue\Redis\RedisQueueManager::class] = function($c) {
            return new Queue\Redis\RedisQueueManager($c['Predis\ClientInterface']);
        };
        $c[Queue\Sqs\SqsQueueManager::class] = function($c) {
            return new Queue\Sqs\SqsQueueManager(
                $c['Aws\Sqs\SqsClient'],
                $c['krak.job.queue.sqs.queue_url_map'],
                $c['krak.job.queue.sqs.receive_options']
            );
        };
        $c[Queue\Sync\SyncQueueManager::class] = function($c) {
            return new Queue\Sync\SyncQueueManager($c[Worker::class]);
        };
        $c[Queue\QueueManagerResolver::class] = function($c) {
            return new Queue\QueueManagerResolver($c);
        };
        $c[Queue\QueueManager::class] = function($c) {
            $provider = $c['krak.job.queue_provider'];
            return $c[Queue\QueueManagerResolver::class]->resolveQueueManager($provider);
        };
        $c[ProcessManager\ProcessManager::class] = function($c) {
            return new ProcessManager\SymfonyProcessManager();
        };
        $c[Scheduler::class] = function($c) {
            $loop = $c['krak.job.schedule_loop'];
            return new Scheduler(
                $c[ProcessManager\ProcessManager::class],
                $c[Queue\QueueManagerResolver::class],
                Mw\compose([
                    Mw\guard('No schedulerLoop was able to resolve a response. Please check your configuration.'),
                    $loop
                ]),
                $c->has(CacheInterface::class) ? $c->get(CacheInterface::class) : null
            );
        };
        $c[SchedulerControl::class] = function($c) {
            return new SchedulerControl($c[CacheInterface::class]);
        };
        $c[Worker::class] = function($c) {
            $consume = $c['krak.job.pipeline.consume'];
            return new Worker(Mw\compose([$consume]));
        };
        $c['krak.job.fail_job'] = function($c) {
            return mw\stack([
                FailJob\completeFailJob(),
                FailJob\retryFailJob(),
            ]);
        };
        $c['krak.job.schedule_loop'] = function($c) {
            $fail_job = $c['krak.job.fail_job'];
            return mw\stack([
                ScheduleLoop\queueScheduleLoop(Mw\compose([$fail_job])),
                ScheduleLoop\schedulerScheduleLoop()
            ]);
        };
        $c['krak.job.pipeline.consume'] = function($c) {
            return mw\stack([
                Pipeline\invokeJobConsume($c[AutoArgs\AutoArgs::class], [
                    'objects' => [$c],
                    'container' => $c->toInterop()
                ])
            ])->push(Pipeline\catchExceptionConsume($c['krak.job.debug']), 1);
        };
        $c['krak.job.pipeline.produce'] = function($c) {
            return mw\stack([
                Pipeline\queueProduce($c[Queue\QueueManagerResolver::class], $c->createQueueProviderMap()),
                Pipeline\pipeJobProduce(),
                Pipeline\timestampProduce(),
                Pipeline\classNameProduce(),
                Pipeline\defaultQueueNameProduce($c['krak.job.default_queue_name']),
            ]);
        };
        $c['krak.job.config'] = [
            'queue' => 'jobs',
            'max_jobs' => 10,
            'sleep' => 10,
        ];
        $c['krak.job.debug'] = false;
        $c['krak.job.default_queue_name'] = 'jobs';
        $c['krak.job.queue_provider'] = 'sync';
        $c['krak.job.queue.doctrine.table_name'] = 'krak_jobs';
        $c['krak.job.queue.sqs.queue_url_map'] = [];
        $c['krak.job.queue.sqs.receive_options'] = [];

        if (!isset($c[AutoArgs\AutoArgs::class])) {
            $c[AutoArgs\AutoArgs::class] = function($c) {
                return new AutoArgs\AutoArgs();
            };
        }
        if (!isset($c['dispatch'])) {
            $c->alias(Dispatch::class, 'dispatch');
        }
        if (!isset($c[Log\LoggerInterface::class])) {
            $c[Log\LoggerInterface::class] = function() {
                return new Log\NullLogger();
            };
        }
    }
}
