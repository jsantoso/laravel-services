<?php

namespace Jsantoso\LaravelServices\SelfTest\Interfaces;

interface SelfTestPluginInterface {
    
    /**
     * Returns the name of the of the test group, such as "RabbitMQ", "Redis", or "Email"
     * @return string
     */
    public function getTestName(): string;
    
    /**
     * 
     * A test class can have 1 or many actions. For example, "Redis" has a few actions to test different write-read scenarios
     * The return type is an array of objects "SelfTestAction" class
     * @return array
     */
    public function getTestActions(): array;
    
}