<?php

namespace Krak\Job\Queue\Sqs;

use Krak\Job\Queue;
use Aws\Sqs\SqsClient;

class SqsQueueManager implements Queue\QueueManager
{
    private $sqs;
    private $queue_url_map;
    private $sqs_receive_options;

    public function __construct(SqsClient $sqs, array $queue_url_map = [], array $sqs_receive_options = []) {
        $this->sqs = $sqs;
        $this->queue_url_map = $queue_url_map;
        $this->sqs_receive_options = $sqs_receive_options;
    }

    public function createQueue($name, array $opts = []) {
        return $this->sqs->createQueue(array_merge([
            'QueueName' => $name,
        ], $opts));
    }

    public function removeQueue($name) {
        $queue_url = $this->getQueueUrlByName($name);
        $this->sqs->deleteQueue(['QueueUrl' => $queue_url]);
    }

    public function getQueue($name) {
        return new SqsQueue($this->sqs, $this->getQueueUrlByName($name), $name, $this->sqs_receive_options);
    }

    private function getQueueUrlByName($name) {
        if (isset($this->queue_url_map[$name])) {
            return $this->queue_url_map[$name];
        }

        $result = $this->sqs->getQueueUrl([
            'QueueName' => $name,
        ]);

        $this->queue_url_map[$name] = $result['QueueUrl'];

        return $this->queue_url_map[$name];
    }
}
