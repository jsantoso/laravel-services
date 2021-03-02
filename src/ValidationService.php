<?php
namespace Jsantoso\LaravelServices;

class ValidationService {
    
    const MAX_INT32 = 2147483647;
    
    public static function validateISODate($time) {
        $regexp = '/^(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2}):(\d{2})(\.\d{1,3})?(Z|((\+|\-)(\d{2}):(\d{2})))$/';
        
        return preg_match($regexp, $time) == 1;
    }
    
    public static function validatePhone($phone) {
        $phone = str_replace(array("-", " ", "(", ")"), "", $phone);
        
        return (strlen($phone) == 10);
    }
    
    public static function validateDate($date, $expectedFormat = "m/d/Y") {
        $regexp = '/^(0[1-9]|1[0-2])\/([012][0-9]|3[01])\/(19|20)(\d{2})$/';
        
        if (preg_match($regexp, $date) != 1) {
            return false;
        }
        
        if (\DateTime::createFromFormat($expectedFormat, $date) === false) {
            return false;
        }
        
        return true;
    }
    
    public static function validateEmail($email) {
        $email = trim($email);
        
        if ($email == "") {
            return false;
        }
        
        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            return false;
        }
        
        return true;
    }
    
    public static function validatePassword($password) {
        
        $minPasswordLength = env('MIN_PASSWORD_LENGTH');
        if ($minPasswordLength == '') {
            $minPasswordLength = 5;
        }
        
        if (strlen($password) < $minPasswordLength) {
            return false;
        }
        
        return true;
    }
    
    public static function isPosInt($value) {
        $value = trim($value);
        return ($value != '' && $value !== null && is_numeric($value) && $value > 0 && preg_match("/^\d+$/", $value) == 1) ? true : false;
    }
    
    public static function isValidInt32Id($value) {
        $value = trim($value);
        if (
            self::isPosInt($value) &&
            $value > 0 &&
            $value <= self::MAX_INT32
        ) {
            return true;
        }
        return false;
    }
    
    public static function isNonNegativeInt($value) {
        $value = trim($value);
        return ($value != '' && $value !== null && is_numeric($value) && $value >= 0 && preg_match("/^\d+$/", $value) == 1) ? true : false;
    }
    
    public static function isFloat($value, $rangeStart, $rangeFinish) {
        $value = trim($value);
        if (
            (is_numeric($value) || is_float($value)) &&
            $rangeStart <= $value &&
            $value <= $rangeFinish
        ) {
            return true;
        } else {
            return false;
        }
    }
    
    public static function validateSex($sex) {
        $sex = trim(strtolower($sex));
        return in_array($sex, array("", "f", "m"));
    }
    
    public static function isValidDayName($day) {
        $day = strtolower($day);
        return in_array($day, ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday']);
    }
    
    public static function isTime($time) {
        $time = trim($time);
        
        $regexp = '/^([01][0-9]|2[0-3])\:([0-5][0-9])\:([0-5][0-9])$/';
        
        if (preg_match($regexp, $time) != 1) {
            return false;
        }
        
        return true;
    }
    
    public static function isIPWithPort($str) {
        $str = trim($str);
        $temp = explode(":", $str);
        if (sizeof($temp) == 2) {
            if (
                filter_Var($temp[0], FILTER_VALIDATE_IP) &&
                self::isPort($temp[1])
            ) {
                return true;
            }
        }
        
        return false;
    }
    
    public static function isPort($value) {
        $value = trim($value);
        return ($value != '' && $value !== null && is_numeric($value) && $value >= 0 && $value <= 65535) ? true : false;
    }
    
    public static function isValidJSON($str) {
        $str = trim($str);
        if ($str == '') {
            return false;
        }
        
        $json = json_decode($str, true);
        if ($json === false || $json === null) {
            return false;
        }
        return true;
    }
    
}
