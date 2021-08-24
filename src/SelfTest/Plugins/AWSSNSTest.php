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
        $output[] = new SelfTestAction("Publish message to AWS SNS topic (" . self::TOPIC_NAME . ") and consume the same message via linked SQS queue (" . self::QUEUE_NAME . ")", "SNSListener", $this->generateSNSListenerTest());

        return $output;
    }
    
    
    private function generateSNSListenerTest() {
        return function(){
            
            try {
                $data = $this->generateRandomData();
                
                $topicARN = "arn:aws:sns:" . env('AWS_REGION') . ":" . env('AWS_ACCOUNT_NUMBER') . ":" . self::TOPIC_NAME;
                \Log::info("Publishing to SNS topic ARN {$topicARN}");
                
                $sender = app()->make(SNSSendService::class);
                $sender->send($topicARN, $data, null);

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