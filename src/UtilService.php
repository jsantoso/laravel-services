<?php
namespace Jsantoso\LaravelServices;

use Jsantoso\LaravelServices\ValidationService;

use JsonSchema\Validator;
use JsonSchema\SchemaStorage;
use JsonSchema\Constraints\Factory;

use Ramsey\Uuid\Uuid;
use Exception;

class UtilService {
    
    public static function splitIds($str, $separator = ',') {
        $output = [];
        
        $temp = explode($separator, trim($str));
        foreach ($temp as $elem) {
            $elem = trim($elem);
            if (ValidationService::isPosInt($elem)) {
                $output[] = $elem;
            }
        }
        
        return $output;
    }
    
    public static function split($str, $separator = ',') {
        $output = [];
        
        $temp = explode($separator, trim($str));
        foreach ($temp as $elem) {
            $elem = trim($elem);
            if ($elem != '') {
                $output[] = $elem;
            }
        }
        
        return $output;
    }
    
    public static function convertDate($inputDate, \DateTimeZone $targetTZ, $format = 'Y-m-d H:i:s', $fallbackValue = '') {
        
        $output = $fallbackValue;
        
        $dateObj = \DateTime::createFromFormat('YmdHis', $inputDate, new \DateTimeZone('UTC'));
        if ($dateObj) {
            $dateObj->setTimezone($targetTZ);
            $output = $dateObj->format($format);        
        }
        return $output;
    }
    
    public static function formatDate($inputDate, $format = 'Y-m-d H:i:s', $fallbackValue = '') {
        $output = $fallbackValue;
        
        $dateObj = \DateTime::createFromFormat('YmdHis', $inputDate, new \DateTimeZone('UTC'));
        if ($dateObj) {
            $output = $dateObj->format($format);        
        }
        return $output;
    }
    
    public static function generateUUID() {
        $uuid = Uuid::uuid4();
        return $uuid->toString();
    }
    
    public static function validateAgainstJSONSchema(object $jsonSchemaObject, $json, ?LoggerInterface $logger) {
        
        $schemaStorage = new SchemaStorage();
        $schemaStorage->addSchema('file://mySchema', $jsonSchemaObject);
        
        $validator = new Validator( new Factory($schemaStorage));
        $validator->validate($json, $jsonSchemaObject);
        
        if (!$validator->isValid()) {
            foreach ($validator->getErrors() as $error) {
                
                if ($logger) {
                    try {
                        $logger->warning("Error from validator: " . json_encode($error));
                    } catch (Exception $ex) {}
                }
                
            }
        }
        
        return $validator->isValid();
    }
    
    public static function tempnam($dir, $prefix) {
        
        $output = false;
        
        try {
            $output = tempnam($dir, $prefix);
            
            if (!$output || !is_readable($output)) {
                $output = false;
            }
        } catch (\Throwable $ex) {
        }
        
        //If it fails, try a few more times until it is successful
        $tries = 0;
        while(!$output && $tries < 5) {
            $tries++;
            try {
                $output = tempnam(sys_get_temp_dir(), $prefix);
                
                if (!$output || !is_readable($output)) {
                    $output = false;
                }
            } catch (\Throwable $ex) {
                sleep(1);
            }
        }
        
        if (!$output) {
            $output = sys_get_temp_dir() . '/' . $prefix . gmdate('U') . '_' . mt_rand(1000, 9999);
            try {
                touch($output);
                if (!$output || !is_readable($output)) {
                    $output = false;
                }
            } catch (\Throwable $ex) {
            }
        }
        
        if (!$output) {
            $output = '/dev/shm/' . $prefix . gmdate('U') . '_' . mt_rand(1000, 9999);
            try {
                touch($output);
            } catch (\Throwable $ex) {

            }
        }
        
        return $output;
    }
    
    public static function tempdir($prefix) {
        $output = null;
        
        do { 
            
            $dirName = sys_get_temp_dir() . '/' . $prefix . gmdate('U') . '_' . mt_rand(1000, 9999);
            if (
                !is_dir($dirName) &&
                mkdir($dirName, 0777, true)
            ) {
                $output = $dirName;
            }
            
        } while (!$output);
        
        return $output;
    }
    
    public static function isValidUUID($value) {
        return Uuid::isValid($value);
    }
    
}
