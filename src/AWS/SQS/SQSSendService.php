<?php

namespace Jsantoso\LaravelServices\AWS\SQS;

use Jsantoso\LaravelServices\AWS\SQS\SQSBase;

class SQSSendService extends SQSBase {
    
    public function send($queueName, $message, array $messageAttributes = [], $delayInSeconds = 0) {
        try {
            
            $this->createClient();
            
            if (!$this->client) {
                throw new \Exception("Failed to initialize AWS SQS client");
            }
            
            $queueUrl = $this->getQueueUrl($queueName);
            
            $data = [
                'QueueUrl'      => $queueUrl,
                'MessageBody'   => $message
            ];
            
            if ($delayInSeconds > 0) {
                $data['DelaySeconds'] = $delayInSeconds;
            }
            
            if (sizeof($messageAttributes) > 0) {
                $data['MessageAttributes'] = $messageAttributes;
            }
            
            return $this->client->sendMessage($data);
            
        } catch (\Exception $ex) {
            $this->log("Failed to send to SQS - " . $ex->getMessage() . PHP_EOL . $ex->getTraceAsString());
            return false;
        }
    }
    
}