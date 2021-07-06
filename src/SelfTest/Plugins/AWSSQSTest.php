<?php

namespace Jsantoso\LaravelServices\SelfTest\Plugins;

use Jsantoso\LaravelServices\SelfTest\Interfaces\SelfTestPluginInterface;
use Jsantoso\LaravelServices\SelfTest\Interfaces\SelfTestAction;
use Jsantoso\LaravelServices\AWS\SQS\SQSSendService;
use Jsantoso\LaravelServices\AWS\SQS\SQSReceiveService;
use Exception;

class AWSSQSTest implements SelfTestPluginInterface {
    
    const QUEUE_NAME = 'self_test_queue';
    
    
    public function getTestName(): string {
        return "SQS Listener";
    }
    
    public function getTestActions(): array {
        $output = [];
        $output[] = new SelfTestAction("Produce message to AWS SQS and consume the same message", "SQSListener", $this->generateSQSListenerTest());

        return $output;
    }
    
    
    private function generateSQSListenerTest() {
        return function(){
            
            try {
                $data = $this->generateRandomData();

                $sender = new SQSSendService();
                $sender->createQueue(self::QUEUE_NAME);
                $sender->send(self::QUEUE_NAME, $data);

                sleep(2);
                
                $receiver = new SQSReceiveService();
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