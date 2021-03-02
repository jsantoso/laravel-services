<?php

namespace Jsantoso\LaravelServices\AWS\S3;

use Aws\S3\S3Client;
use Psr\Log\LoggerInterface;

use App\Services\Platform\AWSConfigService;

class S3ClientService {
    
    const CLIENT_VERSION = 'latest';
    
    private $client;
    private $logger;
    
    /**
     * 
     * @var AWSConfigService
     */
    private $awsConfigService;
    
    public function __construct(AWSConfigService $awsConfigService) {
        
        $this->awsConfigService = $awsConfigService;
    }
    
    public function setLogger(LoggerInterface $logger) {
        $this->logger = $logger;
        return $this;
    }
    
    public function __destruct() {
        unset($this->client);
        unset($this->awsConfigService);
    }
    
    public function initClient($region) {
        if (!$this->client) {
            $credentials = new \Aws\Credentials\Credentials($this->awsConfigService->getAWSCredentialKey(), $this->awsConfigService->getAWSCredentialSecret());
            $this->client = new S3Client([
                'version'       => self::CLIENT_VERSION,
                'region'        => $region,
                'credentials'   => $credentials
            ]);
        }
    }
    
    public function getAWSConfigService() {
        return $this->awsConfigService;
    }

    public function upload($bucket, $destinationPath, $content, Array $options = []) {

        $object = [
            'Bucket' => $bucket,
            'Key'    => $destinationPath,
            'Body'   => $content
        ];

        $object = array_merge($object, $options);

        try {
            $this->client->putObject($object);
        } catch (\Exception $ex) {
            $this->log("Failed to upload file to S3. Object: " . json_encode($object) . " - " . $ex->getMessage());
        }
    }

    public function download($bucket, $s3Path, $destinationPath, $async = false) {
        
        $this->log("S3 download method called for bucket {$bucket} as S3 base path {$s3Path}", "info");
        $this->log($async ? "Download method set to asyncronhous" : "Download method set to syncronhous", "info");
        
        $files = $this->client->getIterator('ListObjects', ['Bucket' => $bucket, 'Prefix' => $s3Path]);
        
        $this->log("Creating {$destinationPath}", "info");
        
        $this->createDir($destinationPath);
        
        $this->log("{$destinationPath} created", "info");
        
        $promises = [];
        $filesToDownload = [];
        
        foreach ($files as $file) {
            $this->log("Current memory usage M1: " . number_format(memory_get_usage()) . " bytes", "info");
            $this->log("Processing S3 path " . json_encode($file), "info");
            
            $pathParts = explode('/', dirname($file['Key']));
            $tempLocalDir = $destinationPath . '/'. end($pathParts);
            $tempLocalFilePath = $tempLocalDir . '/' . basename($file['Key']);

            $this->createDir($tempLocalDir);
            
            $this->log("File destination: {$tempLocalFilePath}", "info");
            
            if (!is_file($tempLocalFilePath) && !is_dir($tempLocalFilePath)) { //Do not redownload existing file just in case it is an incomplete file by another process
            
                $this->log("Destination file does not exist. Begin downloading ...", "info");
                $args = [
                    'Bucket' => $bucket,
                    'Key' => $file['Key'],
                    'SaveAs' => $tempLocalFilePath,
                ];
                
                $this->log("S3 argument: " . json_encode($args), "info");

                try {

                    if ($async) {
                        $promises[] = $this->client->getObjectAsync($args)->then(function() use($tempLocalFilePath) {
                            return $tempLocalFilePath;
                        });
                    } else {
                        
                        $downloadSuccessful = false;
                        $downloadTries = 0;
                        do {
                            try {
                                $downloadTries++;
                                $this->getObject($args);
                                $this->log("Download successful", "info");
                                $downloadSuccessful = true;
                            } catch (\Exception $ex) {
                                $this->log("Download failed: " . $ex->getMessage());
                                $downloadSuccessful = false;
                            }
                            
                            if (!$downloadSuccessful) {
                                sleep(2);
                                $this->log("Sleeping for 2 seconds before trying to download again");
                            }
                            
                        } while (!$downloadSuccessful && $downloadTries < 3);
                        
                        if (!$downloadSuccessful) {
                            throw new \Exception("Failed to download after {$downloadTries} tries");
                        } else {
                            $filesToDownload[] = $tempLocalFilePath;
                        }
                    }

                } catch (\Exception $ex) {
                    throw new \Exception("Could not download file from S3: " . $file['Key'] . " - " . $ex->getMessage());
                }
            } else {
                $filesToDownload[] = $tempLocalFilePath;
            }
        }
        
        unset($files);
        
        return $async ? $promises : $filesToDownload;
    }
    
    public function listDir($bucket, $s3Path, $limit = 10) {
        $output = [];
        
        $results = $this->client->listObjects([
            'Bucket'    => $bucket, 
            'Prefix'    => $s3Path,
            'MaxKeys'   => $limit,
            'Delimiter' => '/'
        ]);
        
        $resultData = $results->toArray();
        if (is_array($resultData)) {
            
            if (isset($resultData['CommonPrefixes'])) {
                foreach ($resultData['CommonPrefixes'] as $elem) {
                    if (isset($elem['Prefix'])) {
                        $output[] = $elem['Prefix'];
                    }
                }
            }
            
            if (isset($resultData['Contents'])) {
                foreach ($resultData['Contents'] as $elem) {
                    if (isset($elem['Key'])) {
                        $output[] = $elem['Key'];
                    }
                }
            }
        }
        
        unset($results);
        unset($resultData);
        
        return $output;        
    }
    
    public function getObject($args) {
        $this->client->getObject($args);
    }
    
    protected function createDir($destinationPath) {
        if (!is_dir($destinationPath) && !mkdir($destinationPath, 0777, true)) {
            throw new \Exception("Failed to create base destination directory: {$destinationPath}");
        }
    }
   
    public function doesObjectExist($bucket, $key) {
        return $this->client->doesObjectExist($bucket, $key);
    }
    
    public function doesPathExist($bucket, $s3Path) {
        $results = $this->client->listObjects([
            'Bucket'    => $bucket, 
            'Prefix'    => $s3Path,
            'MaxKeys'   => 10,
            'Delimiter' => '/'
        ]);
        
        $resultData = $results->toArray();
        if (
            is_array($resultData) && 
            isset($resultData['CommonPrefixes']) &&
            sizeof($resultData['CommonPrefixes']) > 0
        ) {
            return true;
        }
        return false;
    }
    
    public function tagObject($bucket, $s3Path, array $tags, $versionId = null) {
        
        $this->log("S3 tagObject method called for bucket {$bucket}, S3 base path {$s3Path}, version ID {$versionId}, and tags " . json_encode($tags), "info");
        
        $tagSet = [];
        foreach ($tags as $k => $v) {
            $tagSet[] = [
                'Key'   => $k,
                'Value' => $v
            ];
        }
        
        if (sizeof($tagSet) > 0) {
            
            $files = $this->client->getIterator('ListObjects', ['Bucket' => $bucket, 'Prefix' => $s3Path]);
            foreach ($files as $file) {
                $this->log("Processing file for tagging: " . json_encode($file), "info");

                $params = [
                    'Bucket'    => $bucket,
                    'Key'       => $file['Key'],
                    'Tagging'   => [
                        'TagSet'    => $tagSet
                    ]
                ];

                if ($versionId) {
                    $params['VersionId'] = $versionId;
                }
                
                $this->log("Tag parameters: " . json_encode($params), "info");

                try {
                    $result = $this->client->putObjectTagging($params);
                    $this->log("Result from object tagging operation: " . $result['VersionId'], "info");
                } catch (\Exception $ex) {
                    $this->log('Error tagging object: ' . $ex->getMessage());
                }
            }
        }
    }
    
    public function getObjectTags($bucket, $s3Path, $versionId = null) {
        
        $output = [];
        
        $this->log("S3 getObjectTags method called for bucket {$bucket}, S3 base path {$s3Path}, and version ID {$versionId}", "info");
        
        $files = $this->client->getIterator('ListObjects', ['Bucket' => $bucket, 'Prefix' => $s3Path]);
        foreach ($files as $file) {
            $this->log("Processing file for tagging: " . json_encode($file), 'info');
        
            $params = [
                'Bucket'    => $bucket,
                'Key'       => $file['Key']
            ];

            if ($versionId) {
                $params['VersionId'] = $versionId;
            }

            $this->log("Tag parameters: " . json_encode($params), 'info');
            
            try {
                
                $output[$file['Key']] = [];

                $result = $this->client->getObjectTagging($params);
                $this->log('Raw tag set from getObjectTagging: ' . json_encode($result['TagSet']), "info");

                foreach ($result['TagSet'] as $tagElem) {
                    if (
                        array_key_exists('Key', $tagElem) &&
                        array_key_exists('Value', $tagElem)
                    ) {
                        $output[$file['Key']][$tagElem['Key']] = $tagElem['Value'];
                    }
                }

            } catch (\Exception $ex) {
                $this->log('Error getting object tags: ' . $ex->getMessage());
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