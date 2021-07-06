<?php

namespace Jsantoso\LaravelServices\SelfTest\Plugins;

use Jsantoso\LaravelServices\SelfTest\Interfaces\SelfTestPluginInterface;
use Jsantoso\LaravelServices\SelfTest\Interfaces\SelfTestAction;

use Jsantoso\LaravelServices\SelfTest\Mail\SelfTestMail;

class EmailTest implements SelfTestPluginInterface {
    
    const TEST_EMAIL = 'noreply@domainmissing.com';
    
    public function getTestName(): string {
        return "Email";
    }
    
    public function getTestActions(): array {
        $output = [];
        $output[] = new SelfTestAction("Send an email", "sendEmail", $this->generateSendEmailTest());

        return $output;
    }
    
    
    private function generateSendEmailTest() {
        return function(){
            \Mail::to(self::TEST_EMAIL)->send(new SelfTestMail());
            $failures = \Mail::failures();
            return sizeof($failures) == 0;
        };
    }
}