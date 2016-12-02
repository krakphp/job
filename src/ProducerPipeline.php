<?php

namespace Krak\Job;

use function Krak\Mw\composeMwSet;

/** manages a stack of producers to form a pipeline. Producers at the end will be executed first */
class ProducerPipeline extends AbstractPipeline
{
    public function onQueue($queue, callable $produce) {
        return $this->push(onQueueProduce($queue, $produce));
    }
    public function on($name, callable $consumer) {
        return $this->push(onNameProduce($name, $produce));
    }
}
