<?php

namespace Krak\Job;

class Result
{
    const STATUS_COMPLETE = 'complete';
    const STATUS_FAILED = 'failed';

    public $status;
    public $payload;

    public function __construct($status, array $payload = []) {
        $this->status = $status;
        $this->payload = $payload;
    }

    public function withStatus($status) {
        $res = clone $this;
        $res->status = $status;
        return $res;
    }

    public function withPayload(array $payload) {
        $res = clone $this;
        $res->payload = $payload;
        return $res;
    }

    public function isComplete() {
        return $this->status == self::STATUS_COMPLETE;
    }
    public function isFailed() {
        return $this->status == self::STATUS_FAILED;
    }

    public static function failed(array $payload = []) {
        return new self(self::STATUS_FAILED, $payload);
    }

    public static function complete(array $payload = []) {
        return new self(self::STATUS_COMPLETE, $payload);
    }
}
