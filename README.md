# Jobs

Simple yet powerful implementation of Queued Jobs.

## Features

- Consume from multiple queues asynchronously
- Easy to setup
- Easy to integrate with any project
- Incredibly extendable
- Powerful extensions

## Installation

Install with composer at `krak/job`.

## Usage

### Create the Kernel

The kernel is the core manager of the Job library. It's simply a Cargo\\Container decorator with helper methods.

```php
<?php

$kernel = new Krak\Job\Kernel();
```

You can also pass an optional Container instance if you want to use a special container.

#### Configuring the Kernel

You can configure the kernel by wrapping any of the services defined in the container. In addition to the configuration provided by the Cargo\\Container, the kernel provides helper methods to ease customization.

```php
// configure the scheduling loop
$kernel->config([
    'name' => 'Jobs Queue',
    'sleep' => 10,
    'ttl' => 50,
    'max_jobs' => 10,
    'max_retry' => 3,
]);
// configure the queue manager
$kernel->queueManager(function($qm, $c) {
    return Krak\Job\createQueueManager(new Predis\Client());
});
// configure the consumer stack
$kernel->wrap('krak.job.pipeline.consumer', function($consumer) {
    return $consumer->push(myConsumer());
});
// and so on...
```

### Define a Job

Every Job must implement the empty interface `Krak\Job\Job` and have a `handle` method. The `handle` method will be executed once the Job has been consumed for processing.

```php
<?php

namespace Acme\Jobs;

use Krak\Job\Job;
use Acme\ServiceA;

class ProcessJob implements Job
{
    private $id;

    public function __construct($id) {
        $this->id = $id;
    }

    public function handle(ServiceA $service) {
        process($this->id);
    }
}
```

Arguments will automatically wired into the handle method using the AutoArgs package. The Job instance will be serialized, so make sure that the properties of the Job are serializable. It'd also be a good idea to keep the amount of data in a job as small as possible.

### Dispatch a Job

Dispatching jobs is easy using the `Krak\Job\Dispatch`.

```php
<?php

use Krak\Job;

// use the kernel to create a dispatch instance
$dispatch = $kernel['dispatch']; // or $kernel[Job\Dispatch::class];

$dispatch->wrap(new Acme\Jobs\ProcessJob(1))
    ->onQueue('process') // this is optional
    ->withName('process')
    ->dispatch();
```

### Consuming the Jobs

In order to start consuming jobs, you need to do a few things:

1. Register the Commands with your Symfony Console Application

    ```php
    <?php
    // in bin/console

    $app = new Symfony\Component\Console\Application();
    Krak\Job\registerConsole($app, $kernel);
    ```

    At this point, we've registered all of the job commands and added the JobHelper to the helper set.

3. Start the consumer

    ```bash
    ./bin/console job:consume -vvv
    ```

    You can change the verbosity level to suite your needs

## Concepts

The Job library is broken up into several parts: The Kernel, ScheduleLoop, Queues, Dispatch, Console, Pipeline, Jobs

### Kernel

An Kernel implements the `Krak\Job\Kernel` interface and are responsible for configuring and managing everything.

### Scheduler

The scheduler is responsible for scheduling the tasks to run. The `Krak\Job\Scheduler` class actually doesn't contain much logic, it simply starts an infinite loop and passes control the **Schedule Loop**.

The Schedule Loop is a handler that accepts a set of parameters like a logger and configuration and does stuff. The implementation can be anything. We have two main types of schedule loops, Queue loops and Scheduler loops.

The Queue loops manage the scheduling of jobs. They dispatch jobs from the queue to a worker and reap any completed jobs. They also manage the failing of jobs.

Scheduler Loops are responsible for managing other Schedulers. This allows recursive scheduling and asynchronous processing of different queues. Because you can have one scheduler that's managing to two distinct queue schedulers in their own processes.

### Worker

The worker is a simple class that takes a WrappedJob and runs the Consumers on it.

### Consumer

A consumer is a handler that takes a `WrappedJob` instance and returns a Result. We use the Krak\\Mw library to transform a set of Consumer middleware into a single consumer to allow full customization with the Consumers.

### Producer

A producer is the antithesis of a Consumer. A producer is designed to produce a job and push it into a queue. Like consumers, producers are implemented via set of producer middleware.

### Dispatch

The Dispatch is a very simple class/interface designed to wrap Job's into `WrappedJob` and then dispatch them to the producer.

### Queue

The Queuing module handles the actual queueing implementations. There are two main components: Queue Managers and Queues.

**Supported Queues**

- Redis
- Stub

## Cookbook

### Async Scheduling

To perform schedule multiple queues at a time, update the kernel config like this:

```php
$kernel->config([
    'name' => 'Master Scheduler',
    'sleep' => 10,
    'ttl' => 50,
    'schedulers' => [
        [
            'queue' => 'emails',
            'max_jobs' => 20,
        ],
        [
            'queue' => 'orders',
            'max_retry' => 3,
        ]
    ]
]);
```

This will create a master scheduler that will then manage two schedulers which manage a different queue. This will launch two separate processes that manage each queue, so the processing of each queue will be completely asynchronous.
