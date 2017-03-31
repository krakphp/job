<?php

namespace Krak\Job;

use Krak\Cargo;
use Krak\Mw;
use Krak\AutoArgs;

class JobServiceProvider implements Cargo\ServiceProvider
{
    public function register(Cargo\Container $c) {
        $c[Dispatch::class] = function($c) {
            $produce = $c['krak.job.pipeline.produce'];
            return new Dispatch\ProducerDispatch(Mw\compose([$produce]));
        };
        $c[Queue\Sqs\SqsQueueManager::class] = function($c) {
            return new Queue\Sqs\SqsQueueManager(
                $c['Aws\Sqs\SqsClient'],
                $c['krak.job.queue.sqs.queue_url_map'],
                $c['krak.job.queue.sqs.receive_options']
            );
        };
        $c[Queue\Redis\RedisQueueManager::class] = function($c) {
            return new Queue\Redis\RedisQueueManager($c['Predis\ClientInterface']);
        };
        $c[Queue\QueueManager::class] = function($c) {
            $provider = $c['krak.job.queue_provider'];
            switch ($provider) {
            case 'redis': return $c[Queue\Redis\RedisQueueManager::class];
            case 'sqs': return $c[Queue\Sqs\SqsQueueManager::class];
            case 'stub': return new Queue\Stub\StubQueueManager();
            default:
                throw new \InvalidArgumentException('Invalid Queue Provider (krak.job.queue_provider) given.');
            }
        };
        $c[ProcessManager\ProcessManager::class] = function($c) {
            return new ProcessManager\SymfonyProcessManager();
        };
        $c[Scheduler::class] = function($c) {
            $loop = $c['krak.job.schedule_loop'];
            return new Scheduler(
                $c[ProcessManager\ProcessManager::class],
                $c[Queue\QueueManager::class],
                Mw\compose([
                    Mw\guard('No schedulerLoop was able to resolve a response. Please check your configuration.'),
                    $loop
                ])
            );
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
            ])->push(Pipeline\catchExceptionConsume(), 1);
        };
        $c['krak.job.pipeline.produce'] = function($c) {
            return mw\stack([
                Pipeline\queueProduce($c[Queue\QueueManager::class]),
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
        $c['krak.job.default_queue_name'] = 'jobs';
        $c['krak.job.queue_provider'] = 'stub';
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
    }
}
