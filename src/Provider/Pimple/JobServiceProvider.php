<?php

namespace Krak\Job\Provider\Pimple;

use Krak\Job,
    Pimple;

class JobServiceProvider implements Pimple\ServiceProviderInterface
{
    public function register(Pimple\Container $c) {
        $c[Job\Queue\QueueManager::class] = function($c) {
            return Job\createQueueManager(...$c['job.queue.manager.params']);
        };
        $c[Job\Kernel::class] = function($c) {
            $kernel = new Job\Kernel\JobKernel($c[Job\Queue\QueueManager::class]);
            $kernel = new Job\Kernel\PimpleKernel($kernel, $c);
            return $kernel;
        };
        $c[Job\Dispatch::class] = function($c) {
            return $c[Job\Kernel::class]->createDispatch();
        };

        $c['job.kernel'] = $c->raw(Job\Kernel::class);
        $c['job.dispatch'] = $c->raw(Job\Dispatch::class);
        $c['job.queue.manager'] = $c->raw(Job\Queue\QueueManager::class);
        $c['job.queue.manager.params'] = [];
    }
}
