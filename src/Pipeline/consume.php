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

function catchExceptionConsume() {
    return function(Job\WrappedJob $job, $next) {
        try {
            return $next($job);
        } catch (\Exception $e) {
            return Job\failed(['exception' => $e->getMessage()]);
        } catch (\Throwable $e) {
            return Job\failed(['exception' => $e->getMessage()]);
        }
    };
}
