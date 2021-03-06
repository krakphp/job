<?php

namespace Krak\Job\Pipeline;

use Krak\Mw,
    Krak\AutoArgs,
    Krak\Job;

/** Consume
    Each consumer has the following signature

    Result
*/

function invokeJobConsume(AutoArgs\AutoArgs $auto_args, array $context = []) {
    return function(Job\WrappedJob $wrapped) use ($auto_args, $context) {
        $callable = [$wrapped->job, 'handle'];
        if (!isset($context['objects'])) {
            $context['objects'] = [];
        }

        $context['objects'][] = $wrapped;

        $res = $auto_args->invoke($callable, $context);

        if ($res instanceof Job\Result) {
            return $res;
        } else if ($res !== null) {
            return Job\complete($res);
        } else {
            return Job\complete();
        }
    };
}

function catchExceptionConsume($show_stack_trace) {
    return function(Job\WrappedJob $job, $next) use ($show_stack_trace) {
        try {
            return $next($job);
        } catch (\Exception $e) {

        } catch (\Throwable $e) {

        }

        return $show_stack_trace
            ? Job\failed(['exception' => (string) $e])
            : Job\failed(['exception' => $e->getMessage()]);
    };
}
