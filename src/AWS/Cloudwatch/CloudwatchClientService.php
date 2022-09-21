<?php

namespace Jsantoso\LaravelServices\AWS\Cloudwatch;

use Aws\CloudWatch\CloudWatchClient;
use Psr\Log\LoggerInterface;
use Exception;

class CloudwatchClientService {
    const CLIENT_VERSION = '2010-08-01';
    const CLIENT_PROFILE = 'default';
    
    private CloudWatchClient $client;
    private $namespace;
    private $logger;
    
    public function __construct($namespace) {
        $this->namespace = $namespace;
    }
    
    public function __destruct() {
        unset($this->client);
    }
    
    public function putMetricData($metricData) {
        try {
            $this->client->putMetricData([
                'Namespace'     => $this->namespace,
                'MetricData'    => $metricData,
            ]);
        } catch (Exception $ex) {
            $this->log("Exception in CloudWatchClient::putMetricData() - " . $ex->getMessage());
            $this->log($ex->getTraceAsString());
            $this->log(json_encode($metricData, JSON_PRETTY_PRINT));
        }
    }
    
    public function setLogger(LoggerInterface $logger) {
        $this->logger = $logger;
        return $this;
    }
    
    public function initClient($region) {
        if (!$this->client) {
            $this->client = new CloudWatchClient([
                'profile'       => self::CLIENT_PROFILE,
                'version'       => self::CLIENT_VERSION,
                'region'        => $region,
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