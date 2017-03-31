<?php

namespace Krak\Job\Queue\Sqs;

use Aws\Sqs\SqsClient;
use Krak\Job;

class SqsQueue extends Job\Queue\AbstractQueue
{
    private $sqs;
    private $queue_url;
    private $cached_messages;
    private $sqs_receive_options;

    public function __construct(SqsClient $sqs, $queue_url, $name, array $sqs_receive_options) {
        parent::__construct($name);
        $this->sqs = $sqs;
        $this->queue_url = $queue_url;
        $this->sqs_receive_options = $sqs_receive_options;
        $this->cached_messages = [];
    }

    public function enqueue(Job\WrappedJob $job) {
        $params = [
            'QueueUrl' => $this->queue_url,
            'DelayedSeconds' => isset($job->payload['delay']) ? $job->payload['delay'] : null,
            'MessageBody' => base64_encode((string) $job),
        ];
        $params = array_merge(
            array_filter($params),
            isset($job->payload['sqs']) ? $job->payload['sqs'] : []
        );
        $this->sqs->sendMessage($params);
    }

    public function dequeue() {
        if (!count($this->cached_message)) {
            $res = $this->sqs->receiveMessage(array_merge([
                'QueueUrl' => $this->queue_url,
            ], $this->sqs_receive_options));
            $this->cached_messages = $res['Messages'];
        }
        if (!count($this->cached_messages)) {
            return;
        }

        $message = array_shift($this->cached_messages);
        $job = Job\WrappedJob::fromString(base64_decode($message['Body']));
        return $job->withAddedPayload(['_sqs_message' => [
            'MessageId' => $message['MessageId'],
            'ReceiptHandle' => $message['ReceiptHandle'],
        ]]);
    }

    public function complete(Job\WrappedJob $job) {
        $this->sqs->deleteMessage([
            'QueueUrl' => $this->queue_url,
            'ReceiptHandle' => $job->payload['_sqs_message']['ReceiptHandle'],
        ]);
    }
}
