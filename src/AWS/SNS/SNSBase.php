<?php

namespace Jsantoso\LaravelServices\AWS\SNS;

use Aws\Sns\SnsClient;
use Aws\Exception\AwsException;
use Psr\Log\LoggerInterface;

class SNSBase {
    
    const DEFAULT_AWS_REGION = 'us-east-1';
    const DEFAULT_AWS_VERSION = 'latest';
    
    /**
     *
     * @var SnsClient $client
     */
    protected $client;
    
    /**
     * 
     * @var LoggerInterface
     */
    protected $logger;
    
    protected $awsCredentialKey = '';
    protected $awsCredentialSecret = '';
    protected $awsRegion;
    protected $awsVersion;
    
    public function setLogger(LoggerInterface $logger) {
        $this->logger = $logger;
        return $this;
    }
    
    public function setAWSCredentialKey($key) {
        $this->awsCredentialKey = $key;
        return $this;
    }
    
    public function setAWSCredentialSecret($secret) {
        $this->awsCredentialSecret = $secret;
        return $this;
    }
    
    public function setAWSRegion($region) {
        $this->awsRegion = $region;
        return $this;
    }
    
    public function setAWSVersion($version) {
        $this->awsVersion = $version;
        return $this;
    }
            
    protected function createClient() {
        try {
            if (!$this->client) {
                $params = [
                    'region'    => $this->awsRegion ? $this->awsRegion : self::DEFAULT_AWS_REGION,
                    'version'   => $this->awsVersion ? $this->awsVersion : self::DEFAULT_AWS_VERSION,
                    'credentials'   => [
                        'key'       => $this->awsCredentialKey,
                        'secret'    => $this->awsCredentialSecret
                    ]
                ];
                
               
                $this->client = new SnsClient($params);                
            }
        } catch (AwsException $ex) {
            $this->client = null;
            
            $this->log("Exception when trying to create an AWS SNS client - " . $ex->getMessage() . PHP_EOL . $ex->getTraceAsString());
        }
    }
    
    protected function log($message, $messageType = 'warning') {
        if ($this->logger) {
            try {
                $this->logger->$messageType($message);
            } catch (Exception $ex) {

            }
        }
    }
    
}

