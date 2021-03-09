<?php

namespace Jsantoso\LaravelServices\AWS\SQS;

use Jsantoso\LaravelServices\AWS\SQS\SQSReceiveService;
use Illuminate\Support\Facades\Log;

class QueueListenerService {
    
    private $attributeNames = ['SentTimestamp'];
    private $messageAttributeNames = ['All'];
    private $maxNumberOfMessages = 10;
    private $waitTimeSeconds = 5;
    
    private $consumerService;
       
    public function setMaxNumberOfMessages($value) {
        $this->maxNumberOfMessages = $value;
        return $this;
    }
    
    public function setWaitTime($waitTimeSeconds) {
        $this->waitTimeSeconds = $waitTimeSeconds;
        return $this;
    }
    
    public function setAttributeNames(array $values) {
        $this->attributeNames = $values;
        return $this;
    }
    
    public function setMessageAttributeNames(array $values) {
        $this->messageAttributeNames = $values;
        return $this;
    }
    
    public function run($queueName, Callable $function) {       
        
        if (!$this->consumerService) {
            $this->consumerService = new SQSReceiveService();
        }
        
        $this->consumerService->setMaxNumberOfMessages($this->maxNumberOfMessages)
                              ->setWaitTimeSeconds($this->waitTimeSeconds)
                              ->setAttributeNames($this->attributeNames)
                              ->setMessageAttributeNames($this->messageAttributeNames);
        
        do {
            
            $result = $this->consumerService->receive($queueName, false);
            if ($result) {
                foreach ($result as $message) {
                    Log::info("Received message - " . $message['Body']);
                    try {
                        $function($message['Body']);
                        $this->consumerService->deleteMessage($message);
                    } catch (\Exception $ex) {
                        Log::error("Exception when calling self-contained callback function - " . $ex->getMessage());
                    }
                }
            }
            
        } while(true);
    }
}