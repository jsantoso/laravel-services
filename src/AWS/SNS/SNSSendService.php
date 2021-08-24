<?php

namespace Jsantoso\LaravelServices\AWS\SNS;

use Jsantoso\LaravelServices\AWS\SNS\SNSBase;

class SNSSendService extends SNSBase {
    
    public function send($topicARN, $message, $messagStructure = 'json', array $messageAttrributes = [], $subject = null) {
        try {
            
            $this->createClient();
            
            if (!$this->client) {
                throw new \Exception("Failed to initialize AWS SNS client");
            }
            
            if (is_array($message)) {
                $message = json_encode($message);
            }
                  
            $params = [
                'Message'           => $message,
                'TopicArn'          => $topicARN,
            ];
            
            if (!empty($messageAttrributes)) {
                $params['MessageAttributes'] = $messageAttrributes;
            }
            
            if ($messagStructure) {
                $params['MessageStructure'] = $messagStructure;
            }
            
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