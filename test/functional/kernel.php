<?php

use Krak\Job;

$kernel = Job\createKernel(new Predis\Client(['host' => 'krak-job-redis']));
$container = new Pimple\Container();
$container['SplStack'] = function() {
    $stack = new SplStack();
    $stack->push(1);
    $stack->push(2);
    $stack->push(2);
    $stack->push(2);
    return $stack;
};
$kernel = new Job\Kernel\PimpleKernel($kernel, $container);
return $kernel;
