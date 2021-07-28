<?php

namespace Jsantoso\LaravelServices\SelfTest\Plugins;

use Jsantoso\LaravelServices\SelfTest\Interfaces\SelfTestPluginInterface;
use Jsantoso\LaravelServices\SelfTest\Interfaces\SelfTestAction;
use Jsantoso\LaravelServices\SelfTest\Jobs\SelfTestJob;
use Illuminate\Support\Facades\Cache;

class LaravelQueueTest implements SelfTestPluginInterface {
    
    public function getTestName(): string {
        return "Laravel queue worker";
    }
    
    public function getTestActions(): array {
        $output = [];
        $output[] = new SelfTestAction("Laravel queue worker ", "laravelQueueWorker", $this->generateLaravelQueueWorkerTest());

        return $output;
    }
    
    
    private function generateLaravelQueueWorkerTest() {
        return function(){
            try {

                $key = sha1(mt_rand()) . sha1(mt_rand()) . sha1(mt_rand());
                $data = sha1(mt_rand()) . sha1(mt_rand()) . sha1(mt_rand());
                SelfTestJob::dispatch($key, $data);

                sleep(2);
                
                return Cache::get($key) == $data;
                
            } catch (\Exception $ex) {
                \Log::info($ex->getMessage());
                return false;
            }
        };
    }
}
