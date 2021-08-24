<?php

namespace Jsantoso\LaravelServices\SelfTest\Plugins;

use Jsantoso\LaravelServices\SelfTest\Interfaces\SelfTestPluginInterface;
use Jsantoso\LaravelServices\SelfTest\Interfaces\SelfTestAction;
use Jsantoso\LaravelServices\AWS\SNS\SNSSendService;
use Jsantoso\LaravelServices\AWS\SQS\SQSReceiveService;
use Exception;

class AWSSNSTest implements SelfTestPluginInterface {
    
    const TOPIC_NAME = 'self_test_topic';
    const QUEUE_NAME = 'self_test_sns_queue';
    
    public function getTestName(): string {
        return "SNS Listener";
    }
    
    public function getTestActions(): array {
        $output = [];
        $output[] = new SelfTestAction("Produce message to AWS SNS and consume the same message via linked SQS queue", "SNSListener", $this->generateSNSListenerTest());

        return $output;
    }
    
    
    private function generateSNSListenerTest() {
        return function(){
            
            try {
                $data = $this->generateRandomData();

                $sender = app()->make(SNSSendService::class);
                $sender->send(self::TOPIC_NAME, $data, null);

                sleep(2);
                
                $receiver = app()->make(SQSReceiveService::class);
                $receiver->setWaitTimeSeconds(3)
                         ->setMaxNumberOfMessages(10);
                
                $messages = $receiver->receive(self::QUEUE_NAME, true);

                if ($messages) {
                    foreach ($messages as $message) {
                        if ($message['Body'] == $data) {
                            return true;
                        }
                    }
                }

            } catch (Exception $ex) {
                
            }
            return false;
        };
    }
    
    
    private function generateRandomData() {
        return sha1(mt_rand()) . sha1(mt_rand()) . sha1(mt_rand());
    }
}