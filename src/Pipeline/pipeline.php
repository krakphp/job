<?php

/** Common Pipeline middleware for Consuming and Producing */

namespace Krak\Job\Pipeline;

function onPayloadMatch($field, $pattern, $mw) {
    return onPayloadCmp($mw, $field, $val, function($a, $pattern) {
        return preg_match($pattern, $a) == 1;
    });
}

function onPayloadEq($field, $val, $mw) {
    return onPayloadCmp($mw, $field, $val, function($a, $b) {
        return $a == $b;
    });
}

function onPayloadCmp($mw, $field, $val, $cmp) {
    return mw\filter($mw, function($job) use ($field, $val, $cmp) {
        $payload = $job->payload;
        return isset($payload[$field]) && $cmp($payload[$field], $val);
    });
}
