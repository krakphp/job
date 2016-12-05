<?php

namespace Krak\Job;

use Krak\Mw;

/** manages a stack of consumers to form a pipeline. Consumers at the end will be executed first */
abstract class AbstractPipeline
{
    private $middleware;

    public function __construct(array $middleware = []) {
        $this->middleware = $middleware;
    }
    public function push(callable $mw) {
        array_push($this->middleware, $mw);
        return $this;
    }
    public function unshift(callable $mw) {
        array_unshift($this->middleware, $mw);
        return $this;
    }
    public function pop() {
        return array_pop($this->middleware);
    }
    public function shift() {
        return array_shift($this->middleware);
    }
    public function compose() {
        return mw\compose($this->middleware);
    }
}
