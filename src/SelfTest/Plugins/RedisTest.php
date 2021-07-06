<?php

namespace Jsantoso\LaravelServices\SelfTest\Plugins;

use Jsantoso\LaravelServices\SelfTest\Interfaces\SelfTestPluginInterface;
use Jsantoso\LaravelServices\SelfTest\Interfaces\SelfTestAction;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class RedisTest implements SelfTestPluginInterface {
    
    public function getTestName(): string {
        return "Redis";
    }
    
    public function getTestActions(): array {
        $output = [];
        $output[] = new SelfTestAction("Write random data and read it back", "write", $this->generateWriteTest());
        $output[] = new SelfTestAction("Write a random data with short TTL and read after expiry", "expiry", $this->generateExpiryTest());
        $output[] = new SelfTestAction("Write a random data and delete it", "delete", $this->generateDeleteTest());

        return $output;
    }
    
    
    private function generateWriteTest() {
        return function(){
            $key = sha1(mt_rand());
            $value = $this->generateRandomData();

            $expiresAt = Carbon::now()->addSeconds(5);
            Cache::put($key, $value, $expiresAt);

            $readValue = Cache::get($key);

            if ($readValue != null && $readValue == $value) {
                return true;
            }

            return false;
        };
    }
    
    private function generateExpiryTest() {
        return function() {
            $key = sha1(mt_rand());
            $value = $this->generateRandomData();

            $expiresAt = Carbon::now()->addSeconds(3);
            Cache::put($key, $value, $expiresAt);
            sleep(5);

            $readValue = Cache::get($key);
            if (!Cache::has($key) && $readValue == null) {
                return true;
            }
            return false;
        };
    }
    
    private function generateDeleteTest() {
        return function() {
        
            $key = sha1(mt_rand());
            $value = $this->generateRandomData();

            $expiresAt = Carbon::now()->addSeconds(5);
            Cache::put($key, $value, $expiresAt);

            $readValue1 = Cache::get($key);
            if (Cache::has($key) && $readValue1 == $value) {
                Cache::forget($key);
                $readValue2 = Cache::get($key);
                if (!Cache::has($key) && $readValue2 == null) {
                    return true;
                }
            }

            return false;
        };
    }
    
    private function generateRandomData() {
        return sha1(mt_rand()) . sha1(mt_rand()) . sha1(mt_rand());
    }
    
}