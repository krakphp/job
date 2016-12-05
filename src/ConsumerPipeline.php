<?php

namespace Krak\Job;

/** manages a stack of consumers to form a pipeline. Consumers at the end will be executed first */
class ConsumerPipeline extends AbstractPipeline
{
    public function onQueue($queue, callable $consumer) {
        return $this->push(onQueueConsumer($queue, $consumer));
    }
    public function on($name, callable $consumer) {
        return $this->push(onNameConsumer($name, $consumer));
    }
}
