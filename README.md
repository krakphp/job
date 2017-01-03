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

The kernel is the core manager of the Job library. It holds the configuration and acts like a factory for the various components.

```php
<?php

$kernel = Krak\Job\createKernel(new \Predis\Client());
```

### Define a Job

Every Job must implement the empty interface `Krak\Job\Job` and have a `handle` method. The `handle` method will be executed once the Job has been consumed for processing.

```php
<?php

namespace Acme\Jobs;

use Krak\Job\Job;

class ProcessJob implements Job
{
    private $id;

    public function __construct($id) {
        $this->id = $id;
    }

    public function handle() {
        process($this->id);
    }
}
```

### Dispatch a Job

Dispatching jobs is easy using the `Krak\Job\Dispath`.

```php
<?php

// use the kernel to create a dispatch instance
$dispatch = $kernel->createDispatch();

$dispatch->wrap(new Acme\Jobs\ProcessJob(1))
    ->onQueue('process') // this is optional
    ->withName('process')
    ->dispatch();
```

### Consuming the Jobs

In order to start consuming jobs, you need to do a few things:

1. Create your jobs.yml file to configure the scheduling of the queues.

    ```yaml
    name: "Jobs Scheduler"
    queue: "process"
    ttl: 120 # optional, the max time the scheduler will run for before exiting
    sleep: 30 # sleep for 30 seconds after every loop
    max_jobs: 10 # optional, the max number of jobs to run at once
    ```

2. Register the Commands with your Symfony/Laravel Console Application

    ```php
    <?php
    // in bin/console

    $app = new Symfony\Component\Console\Application();
    Krak\Job\registerConsole($app, $kernel);
    ```

    At this point, we've registered all of the job commands and added the JobHelper to the helper set.

3. Start the consumer

    ```bash
    ./bin/console job:consume jobs.yml -vvv
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

The Queuing module handles the actual queueing implementations.

## Cookbook

### Configuring the Kernel

```php
<?php

$kernel = Krak\Job\createKernel();
$kernel->producer(function($stack) {
    // add any middleware
    $stack->push(autoQueueNameProduce('Acme\Jobs\'));
    return $stack;
});
$kernel->consumer(function() {});
$kernel->scheduleLoop(function() {});
```

### Pimple Integration

You can easily integrate your Job Kernel with pimple which allows you to use pimple services as middleware in any middleware stack and also allows for better invocation of the Job handler.

```php
<?php

$kernel = Krak\Job\createKernel(/* ... */);
$kernel = new Krak\Job\Kernel\PimpleKernel($kernel, new Pimple\Container());
```

Now, with this wrapped kernel, you can do the following:

```php
<?php

$kernel->producer(function($stack) {
    $stack->push('some-pimple-service-id-of-a-middleware');
    return $stack;
});

// also, you can use the AutoArgs functionality in your job handlers.
$container[AcmeProcessor::class] = function() {};

class AcmeJob implements Krak\Job\Job {
    public function handle(AcmeProcessor $processor, Pimple\Container $container) {

    }
}
```

Internally, it uses [Krak\\AutoArgs](https://github.com/krakphp/auto-args) to implement the auto arguments.

### Async Scheduling

To perform schedule multiple queues at a time, you can create a jobs.yml file like this.

```yaml
name: "Master Scheduler"
sleep: 5
schedulers:
    - queue: "jobs"
      max_jobs: 10
    - queue: "jobs1"
```

This will create a master scheduler that will then manage two schedulers which manage a different queue.
