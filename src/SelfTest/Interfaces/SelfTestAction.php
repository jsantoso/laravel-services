<?php

namespace Jsantoso\LaravelServices\SelfTest\Interfaces;

use Exception;

class SelfTestAction {
    
    private $id;
    private $actionLabel;
    private $actionName;
    private $actionTest;
    
    public function __construct($actionLabel, $actionName, callable $actionTest) {
        $this->id = sha1(random_int(10000, PHP_INT_MAX));
        $this->actionLabel = $actionLabel;
        $this->actionName = $actionName;
        $this->actionTest = $actionTest;
    }
    
    public function getActionLabel() {
        return $this->actionLabel;
    }
    
    public function getActionName() {
        return $this->actionName;
    }
    
    public function getId() {
        return $this->id;
    }
    
    public function runActionTest() {
        $output = false;
        try {
            $actionTest = $this->actionTest;
            $output = $actionTest() ? true : false;
        } catch (Exception $ex) {
            $output = false;
        }
        
        return $output;
    }
}