<?php

namespace Jsantoso\LaravelServices\AWS\SNS;

use Jsantoso\LaravelServices\AWS\SNS\SNSBase;

class SNSSendService extends SNSBase {
    
    public function send($topicARN, $message, $subject = null) {
        try {
            
            $this->createClient();
            
            if (!$this->client) {
                throw new \Exception("Failed to initialize AWS SNS client");
            }
                  
            $params = [
                'Message'   => $message,
                'TopicArn'  => $topicARN,
            ];
            
            if ($subject) {
                $params['Subject'] = $subject;
            }
            
            return $this->client->publish($params);
            
        } catch (\Exception $ex) {
            $this->log("Failed to publish to SNS - " . $ex->getMessage() . PHP_EOL . $ex->getTraceAsString());
            return false;
        }
    }
    
}