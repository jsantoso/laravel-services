<?php

namespace Jsantoso\LaravelServices\Traits;

use Illuminate\Http\Request;
use Jsantoso\LaravelServices\ValidationService;
use Exception;

trait PayloadValidation {
    
    public static $VALIDATE_FOR_POSITIVE_INTEGER = 1;
    public static $VALIDATE_FOR_NON_NEGATIVE_INTEGER = 2;
    public static $VALIDATE_FOR_ISO_DATE = 3;
    public static $VALIDATE_FOR_JSON = 4;
    
    public function generateErrorResponse($code, $message, $contentType = 'text/plain') {
        return response(json_encode($message), $code)
                ->header('Content-Type', $contentType);
    }
    
    public function generateSuccessResponse($code, $message, $contentType = 'application/json') {
        return response($message, $code)
                ->header('Content-Type', $contentType);
    }
    
    
    public function getRequestPayload(Request $request) {
        $payload = null;
        
        $requestContent = trim($request->getContent());
        if ($requestContent != '') {
            $payload = json_decode($requestContent, true);
        } else {
            $postedData = $request->all();
            if ($postedData && sizeof($postedData) > 0) {
                $payload = $postedData;
            }
        }

        if ($payload === null) {
            throw new Exception("Request body cannot be empty", 400);
        }
        
        return $payload;
    }
    
    public function getRequiredPayloadAttribute(array $payload, $attributeName, $validationRule = null) {
        if (!isset($payload[$attributeName])) {
            throw new Exception("The attribute '{$attributeName}' must be set");
        }
        $attributeValue = trim($payload[$attributeName]);
        if ($attributeValue == '') {
            throw new Exception("The attribute '{$attributeName}' cannot be empty");
        }
        
        if ($validationRule) {
            $this->validationAttributeValue($attributeValue, $attributeName, $validationRule);
        }
        
        return $attributeValue;
    }
    
    public function getOptionalPayloadAttribute(array $payload, $attributeName, $validationRule = null) {
        $attributeValue = null;
        
        if (isset($payload[$attributeName])) {
            $attributeValue = trim($payload[$attributeName]);
            
            if ($attributeValue != '' && $validationRule) {
                $this->validationAttributeValue($attributeValue, $attributeName, $validationRule);
            }
        }
    
        return $attributeValue;
    }
    
    public function validationAttributeValue($attributeValue, $attributeName, $validationRule) {
        switch ($validationRule) {
            case self::$VALIDATE_FOR_POSITIVE_INTEGER:
                if (!ValidationService::isPosInt($attributeValue)) {
                    throw new Exception("The attribute '{$attributeName}' must be an integer bigger than zero");
                }
                break;
                
            case self::$VALIDATE_FOR_NON_NEGATIVE_INTEGER:
                if (!ValidationService::isNonNegativeInt($attributeValue)) {
                    throw new Exception("The attribute '{$attributeName}' must be an integer bigger or equal to zero");
                }
                break;
                
            case self::$VALIDATE_FOR_ISO_DATE:
                $obj = \DateTime::createFromFormat('Y-m-d H:i:s', $attributeValue);
                if (!$obj) {
                    throw new Exception("The attribute '{$attributeName}' must be using date format yyyy-mm-dd hh:mm:ss");
                }
                break;
                
            case self::$VALIDATE_FOR_JSON:
                if (is_string($attributeValue)) {
                    $decoded = json_decode($attributeValue);
                    if (!$decoded) {
                        throw new Exception("The attribute '{$attributeName}' must be a valid JSON format");
                    }
                }
                break;
        }
    }
    
}