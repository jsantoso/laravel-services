<?php

namespace Jsantoso\LaravelServices\SelfTest\Plugins;

use Jsantoso\LaravelServices\SelfTest\Interfaces\SelfTestPluginInterface;
use Jsantoso\LaravelServices\SelfTest\Interfaces\SelfTestAction;

class TempFileTest implements SelfTestPluginInterface {
    
    public function getTestName(): string {
        return "Temporary file";
    }
    
    public function getTestActions(): array {
        $output = [];
        $output[] = new SelfTestAction("Create unique temporary file in /tmp directory", "tempnam", $this->generateTempnamTest());
        $output[] = new SelfTestAction("Write and read to a temporary file in /tmp directory", "temp_write", $this->generateWriteTest());
        $output[] = new SelfTestAction("Create a temporary file in /tmp directory and delete", "temp_delete", $this->generateDeleteTest());

        return $output;
    }
    
    
    private function generateTempnamTest() {
        return function() {

            $output = false;

            $file = $this->createTempFile();
            if (
                $file && 
                is_file($file) && 
                is_writable($file)
            ) {
                $output = true;
            }

            if ($file) {
                unlink($file);
            }

            return $output;
        };
    }
    
    private function generateWriteTest() {
        return function() {
            $output = false;

            $file = $this->createTempFile();
            $data = $this->generateRandomData();

            file_put_contents($file, $data);
            $readData = file_get_contents($file);

            if ($readData && $readData == $data) {
                $output = true;
            }

            unlink($file);
            return $output;
        };
    }
    
    private function generateDeleteTest() {
        return function() {
            $output = false;

            $file = $this->createTempFile();
            if ($file) {

                unlink($file);
                if (!is_file($file)) {
                    $output = true;
                }
            }

            return $output;
        };
    }
    
   
    private function createTempFile() {
        return @tempnam(sys_get_temp_dir(), 'self_test_');
    }
    
    private function generateRandomData() {
        return sha1(mt_rand()) . sha1(mt_rand()) . sha1(mt_rand());
    }
}