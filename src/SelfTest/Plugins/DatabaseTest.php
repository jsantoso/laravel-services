<?php

namespace Jsantoso\LaravelServices\SelfTest\Plugins;

use Jsantoso\LaravelServices\SelfTest\Interfaces\SelfTestPluginInterface;
use Jsantoso\LaravelServices\SelfTest\Interfaces\SelfTestAction;
use Illuminate\Support\Facades\DB;

class DatabaseTest implements SelfTestPluginInterface {
    
    public function getTestName(): string {
        return "Database connections";
    }
    
    public function getTestActions(): array {
        $output = [];
        $output[] = new SelfTestAction("Connection to database as described in .env file", "connectAndAccess", $this->generateDbConnectionTest());

        return $output;
    }
    
    
    private function generateDbConnectionTest() {
        return function(){
            try {

                $pdo = DB::connection()->getPdo();

                $output = ($pdo != null) ? true : false;

                return $output;
            } catch (\Exception $ex) {
                \Log::info($ex->getMessage());
                return false;
            }
        };
    }
}