<?php

namespace Jsantoso\LaravelServices\AWS\SQS;

use Jsantoso\LaravelServices\AWS\SQS\SQSBase;

class SQSReceiveService extends SQSBase {
    
    private $attributeNames = ['SentTimestamp'];
    private $messageAttributeNames = ['All'];
    private $maxNumberOfMessages = 10;
    private $waitTimeSeconds = 5;
    private $queueUrl;
    
    public function setAttributeNames(array $values) {
        $this->attributeNames = $values;
        return $this;
    }
    
    public function setMessageAttributeNames(array $values) {
        $this->messageAttributeNames = $values;
        return $this;
    }
    
    public function setMaxNumberOfMessages($value) {
        $this->maxNumberOfMessages = $value;
        return $this;
    }
    
    public function setWaitTimeSeconds($value) {
        $this->waitTimeSeconds = $value;
        return $this;
    }
    
    public function receive($queueName, $autoDeleteReceivedMessages = true) {
        
        $result = null;
        try {
            
            $this->createClient();
            
            if (!$this->client) {
                throw new \Exception("Failed to initialize AWS SQS client");
            }
            
            if (!$this->queueUrl) {
                $this->queueUrl = $this->getQueueUrl($queueName);
            }
            
            $result = $this->client->receiveMessage([
                'AttributeNames'        => $this->attributeNames,
                'MaxNumberOfMessages'   => $this->maxNumberOfMessages,
                'MessageAttributeNames' => $this->messageAttributeNames,
                'QueueUrl'              => $this->queueUrl,
                'WaitTimeSeconds'       => $this->waitTimeSeconds,
            ]);
            
        } catch (\Exception $ex) {
            $this->log("Exception when receiveing SQS message from {$this->queueUrl} - " . $ex->getMessage());
            sleep(3);
            
            $result = null;
        }
        
        if ($result) {
            $messages = $result->get('Messages');

            if ($autoDeleteReceivedMessages && $messages) {
                foreach ($messages as $message) {
                    $this->deleteMessage($message);
                }
                
                reset($messages);
            }
            
            return $messages;
        }
        
        return null;
    }
    
    public function deleteMessage($message) {
        try {
            $this->client->deleteMessage([
                'QueueUrl'      => $this->queueUrl,
                'ReceiptHandle' => $message['ReceiptHandle']
            ]);
        } catch (\Exception $ex) {
            $this->log("Exception when deleting a received message - " . json_encode($message) . " - " . $ex->getMessage());
        }
    }
}