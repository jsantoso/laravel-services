<?php

namespace Jsantoso\LaravelServices\AWS\SQS;

use Jsantoso\LaravelServices\AWS\SQS\SQSReceiveService;
use Illuminate\Support\Facades\Log;

use Exception;

class QueueListenerService {
    
    public function __construct(SQSReceiveService $consumerService = null) {
        if ($consumerService) {
            $this->consumerService = $consumerService;
        }
    }
    
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
            throw new Exception("consumer service is not set. You can set it from constructor");
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
                        $this->consumerService->deleteMessage($message);
                        $function($message['Body']);
                    } catch (\Exception $ex) {
                        Log::error("Exception when calling self-contained callback function - " . $ex->getMessage());
                    }
                }
            }
            
        } while(true);
    }
}