<?php

namespace Jsantoso\LaravelServices\AWS\SQS;

use Aws\Sqs\SqsClient;
use Aws\Exception\AwsException;
use Psr\Log\LoggerInterface;

class SQSBase {
    
    const DEFAULT_AWS_REGION = 'us-east-1';
    const DEFAULT_AWS_VERSION = 'latest';
    
    /**
     *
     * @var SqsClient $client
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
    protected $sqsEndpoint;
    
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
    
    public function setSQSEndpoint($sqsEndpoint) {
        $this->sqsEndpoint = $sqsEndpoint;
        return $this;
    }
 
    public function createQueue($queueName) {
        try {
            $this->createClient();
            
            if (!$this->client) {
                throw new \Exception("Client is not initialized");
            }
            
            $queue = $this->client->createQueue([
                'QueueName'     => $queueName,
                'Attributes'    => [
                    'DelaySeconds'       => 0,
                    'MaximumMessageSize' => (256 * 1024), // Max of 256 KB
                ]
            ]);
            
            return $queue;
        } catch (AwsException $ex) {
            $this->log("Exception when trying to create an AWS SQS queue '{$queueName}' - " . $ex->getMessage());
        }
        
        return null;
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
                
                if ($this->sqsEndpoint != '') {
                    $params['endpoint'] = $this->sqsEndpoint;
                }
                
                $this->client = new SqsClient($params);                
            }
        } catch (AwsException $ex) {
            $this->client = null;
            
            $this->log("Exception when trying to create an AWS SQS client - " . $ex->getMessage() . PHP_EOL . $ex->getTraceAsString());
        }
    }
    
    public function getQueueUrl($queueName) {
        
        $output = null;
        $result = null;

        if ($this->client) {
            try {
            
                $result = $this->client->getQueueUrl([
                    'QueueName' => $queueName
                ]);
            } catch (\Exception $ex) {
                $this->log("Failed when getting queue URL for {$queueName} - " . $ex->getMessage());
            }

            if ($result) {
                $output = $result['QueueUrl'];
            }
        }

        return $output;
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

