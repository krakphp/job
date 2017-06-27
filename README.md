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
    'name' => 'jobs', // name of the queue
    'sleep' => 10, // duration in seconds the scheduler will sleep for after every iteration
    'ttl' => 50, // max duration of the scheduler before dying
    'max_jobs' => 10, // max number of processes to launch at once
    'max_retry' => 3, // max number of retries before just giving up on a failed job
    'batch_size' => 5, // max number of jobs to process in a given batch
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
    ->delay(3600) // will delay the sending of this job for 1 hour (not all queues support delay)
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

2. Start the consumer

    ```bash
    ./bin/console job:consume -vvv
    ```

    You can change the verbosity level to suite your needs

### Restarting & Stopping Jobs

There are times when you want to restart the running consumer or even restart the system. To enable this, you need to integrate `Psr\SimpleCache` into the kernel.

Enabling cache:

```php
<?php

$kernel['Psr\SimpleCache\CacheInterface'] = function($c) {
    // return any CacheInterface
    return new Symfony\Component\Cache\Simple\RedisCache($c['Predis\ClientInterface']);
};
```

Once cache is enabled, then you'll have access to the following commands: `job:stop`, `job:restart`, `job:status`, and `job:reset`.

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

- Doctrine
- Redis
- Sqs
- Stub
- Sync

#### Doctrine

Doctrine requires the `doctrine/dbal` library to be installed.

```php
$kernel['Doctrine\DBAL\Connection'] = function() {
    return Doctrine\DBAL\DriverManager::getConnection([
        /* connection params */
    ]);
};
$kernel['krak.job.queue_provider'] = 'doctrine';
$kernel['krak.job.queue.doctrine.table_name'] = 'krak_jobs';
```

Once that's setup, you'll need to perform the database migration to initialize the jobs table. The `Krak\Job\Queue\Doctrine\JobMigration` class is a utility that will facilitate running the migration.

If you already using Doctrine Migrations in your project, you can use simply use the following methods:

```php
// in your migration class
public function up(Schema $schema) {
    $migration = new Krak\Job\Queue\Doctrine\JobMigration('krak_jobs');
    $migration->up($schema);
}

public function down(Schema $schema) {
    $migration = new Krak\Job\Queue\Doctrine\JobMigration('krak_jobs');
    $migration->down($schema);
}
```

Also, you can simply run the following php code to migrate your table up or down

```php
$conn = $kernel['Doctrine\DBAL\Connection'];
$migration = $kernel['Krak\Job\Queue\Doctrine\JobMigration'];

// up
$migration->migrateUp($conn);
// or down
// $migration->migrateDown($conn);
```

#### Redis

Redis requires the `predis/predis` library to be installed. You then just set the queue manager via:

```php
$kernel->queueManager(function() {
    return createQueueManager(new Predis\Client());
});
```

or

```php
$kernel['Predis\ClientInterface'] = function() {
    return new Predis\Client();
};
$kernel['krak.job.queue_provider'] = 'redis';
```

#### Sqs

Sqs requires the aws sdk to be installed `aws/aws-sdk-php`. You can set the queue manager via:

```php
$kernel->queueManager(function() {
return createQueueManager(new Aws\Sqs\SqsClient(), /* optional*/ ['queue-name' => '{queue-url}'], /* optional */ $sqs_receive_options);
});
```

or

```php
$kernel['Aws\Sqs\SqsClient'] = function() {
    return new Aws\Sqs\SqsClient();
};
$kernel['krak.job.queue.sqs.queue_url_map'] = ['queue-name' => '{queue-url}'];
$kernel['krak.job.queue.sqs.receive_options'] = ['VisibilityTimeout' => 10];
$kernel['krak.job.queue_provider'] = 'sqs';
```

The `queue_url_map` is a cache that will be used to lookup the sqs queue url from the queue name given. This cache is optional and will be populated at runtime if not set.

##### Message Configuration

You can configure how you send messages with configuration when you wrap and dispatch the job.

```php
$dispatch->wrap(new MyJob())->with('sqs', ['AnySendMessageParamer' => 'Value'])->dispatch();
```

#### Stub

The stub queue is essentially a noop queue provider. It doesn't enqueue or consume any jobs given to it.

```php
$kernel['krak.job.queue_provider'] = 'stub';
```

#### Sync

The sync(chronous) queue provider will consume your jobs synchronously in the calling thread instead of dispatching them to an external service to be consumed in a different process asynchronously. This is useful for debugging and development purposes.

This is also the default queue provider since it will work out of the box and requires no configuration.

```php
$kernel['krak.job.queue_provider'] = 'sync';
```

## Cookbook

### Async Scheduling

To perform schedule multiple queues at a time, update the kernel config like this:

```php
$kernel->config([
    'name' => 'Master Scheduler',
    'sleep' => 10,
    'schedulers' => [
        [
            'queue' => 'emails',
            'max_jobs' => 20,
            'respawn' => true, // will be respawned after exiting
            'ttl' => 50,
        ],
        [
            'queue' => 'orders',
            'max_retry' => 3,
        ]
    ]
]);
```

This will create a master scheduler that will then manage two schedulers which manage a different queue. This will launch two separate processes that manage each queue, so the processing of each queue will be completely asynchronous.

## Configuration Options

### sleep

The longer the process will sleep, the less resources it will take. The queue provider will be pulled once every `sleep` number of seconds. If you plan on only processing a few jobs on the given queue, then you can set this to a higher value. Conversely, if the queue will be processing at a high throughput, you'll need to set this to smaller value like 1 or 2 seconds.

### ttl

This is how long in seconds the scheduler should run before stopping. This is useful in conjunction with the `respawn` option to have the application state refresh. For example, in development, you might want to set a short ttl with a restart so that you don't have to keep restarting the scheduler to test changes.

### respawn

The respawn is a boolean that determines if a parent scheduler will respawn a child queue scheduler once it's been killed.

### max_jobs

This is the maximum number of worker processes that will be running at the same time. If your jobs take a longer time for completion or if the queue will be consuming a very high number of jobs, then setting this value to greater than 1 can greatly speed up the overall processing of the jobs because they will be done in parallel.

Keep in mind that each process consumes memory and has a bit of overhead so this should be tuned with that in mind.

### max_retry

Max number of retries before just giving up on a failed job

### batch_size

This is max number of jobs to process in a given batch. Every worker process that is created handles a batch of jobs. The higher the batch size helps lower the memory footprint of the system since fewer processes will be created when the batch size is higher.

This works great for jobs that finish execution relatively quickly (less than 5 seconds); however, if the jobs take much longer to execute, then you're better off increasing the max_jobs and lowering this value to around 1.
