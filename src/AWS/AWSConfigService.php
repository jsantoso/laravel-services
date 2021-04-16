<?php

namespace Jsantoso\LaravelServices\AWS;

class AWSConfigService {
    
    const S3_PROTOCOL = 's3://';
    
    public function __construct() {
        
    }

    public function getAWSCredentialKey() {
        $key = trim(getenv('AWS_CREDENTIAL_KEY'));
        if ($key == '') {
            $key = env('AWS_CREDENTIAL_KEY');
        }
        return $key;
    }
    
    public function getAWSCredentialSecret() {
        $secret = trim(getenv('AWS_CREDENTIAL_SECRET'));
        if ($secret == '') {
            $secret = env('AWS_CREDENTIAL_SECRET');
        }
        return $secret;
    }


}