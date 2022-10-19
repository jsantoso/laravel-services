<?php

namespace Jsantoso\LaravelServices\AWS\SecretManager;

use Aws\SecretsManager\SecretsManagerClient;
use Psr\Log\LoggerInterface;
use Exception;

class SecretManagerService {
    const CLIENT_VERSION = '2017-10-17';
    
    protected $client;
    protected $awsCredentialKey = '';
    protected $awsCredentialSecret = '';
    protected $namespace;
    protected $logger;
    
    public function __construct() {
        $this->client = null;
    }
    
    public function setAWSCredentialKey($key) {
        $this->awsCredentialKey = $key;
        return $this;
    }
    
    public function setAWSCredentialSecret($secret) {
        $this->awsCredentialSecret = $secret;
        return $this;
    }
    
    public function __destruct() {
        unset($this->client);
    }
    
    public function setLogger(LoggerInterface $logger) {
        $this->logger = $logger;
        return $this;
    }
    
    public function getSecret($secretName) {
        $output = null;
        
        try {
            $result = $this->client->getSecretValue([
                'SecretId'     => $secretName,
            ]);
            
            if (isset($result['SecretString'])) {
                $output = $result['SecretString'];
            } else {
                $output = base64_decode($result['SecretBinary']);
            }
            
        } catch (Exception $ex) {
            $this->log("Exception in SecretManagerService::getSecret({$secretName}) - " . $ex->getMessage());
            $this->log($ex->getTraceAsString());
        }
        
        return $output;
    }
    
    public function initClient($region) {
        if (!$this->client) {
            $this->client = new SecretsManagerClient([
                'version'       => self::CLIENT_VERSION,
                'region'        => $region,
                'credentials'   => [
                    'key'       => $this->awsCredentialKey,
                    'secret'    => $this->awsCredentialSecret
                ]
            ]);
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