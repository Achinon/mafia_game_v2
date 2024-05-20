<?php

namespace App\Utils;

use Exception;

class Utils
{
    public static function strContains(string $subject,
                                       string $string,
                                       bool $caseInsensitive = false): bool
    {
        if($caseInsensitive){
            $string = strtoupper($string);
            $subject = strtoupper($subject);
        }
        $a = explode($string, $subject);
        if (count($a) > 1)
            return true;

        return false;
    }

    public static function refreshWindow(): void
    {
        print '<meta http-equiv="refresh" content="1">';
    }
    
    public static function hasValidPasswordStrength($password): bool
    {
        static $validPasswordStrength = null;
        if ($validPasswordStrength === null) {
            $validPasswordStrength = strlen($password) >= 9 &&
              preg_match('@[A-Z]@', $password) &&
              preg_match('@[a-z]@', $password) &&
              preg_match('@[0-9]@', $password) &&
              preg_match('@[\W]@', $password);
        }
        
        return $validPasswordStrength;
    }
    
    public static function generateRandomNumberString($length): string
    {
        return static::generateString('0987654321', $length);
    }

    public static function generateRandomString($length = 10, $hex = true): string
    {
        $numbers = '1234567890';
        $letters = 'abcdefghijklmnopqrstuvwxyz';
        $lettersBig = strtoupper($letters);
        $lettersHex = substr($lettersBig, 0, 6);
        
        $string = $hex ?
          $numbers . $lettersHex :
          $numbers . $letters . $lettersBig;
        return static::generateString($string, $length);
    }
    
    private static function generateString ($string, $length)
    {
        $charactersLength = strlen($string);
        $randomString = '';
        
        for ($i = 0; $i < $length; $i++)
            $randomString .= $string[rand(0, $charactersLength - 1)];
        
        return $randomString;
    }

    public static function friendlyString($length = 5,
                                          $divider = "_",
                                          $hexadecimal = true): string
    {
        return self::generateRandomString($length, $hexadecimal) . $divider .
            self::generateRandomString(floor($length / 3), $hexadecimal) . $divider .
            self::generateRandomString(ceil($length / 2), $hexadecimal);
    }

    /**
     * @param mixed ...$params
     *
     * @return void
     */
    public static function json(...$params): void
    {
        echo '<pre>';
        foreach ($params as $param)
            json_encode($param, JSON_PRETTY_PRINT);
        echo '</pre>';
        exit();
    }

    /**
     * @param mixed ...$params
     *
     * @return void
     */
    public static function print(...$params): void
    {
        echo '<pre>';
        foreach ($params as $param)
            print_r($param);
        echo '</pre>';
        exit();
    }

    /**
     * @param mixed ...$params
     *
     * @return void
     */
    public static function echo(...$params): void
    {
        echo '<pre>';
        foreach ($params as $k => $v) {
            echo "$k: ";
            print_r($v) . PHP_EOL;
        }
        echo '</pre>';
        exit();
    }

    /**
     * @param mixed ...$params
     *
     * @return void
     */
    public static function dump(...$params): void
    {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 2);
        
        // Get the caller's file and line
        $b = $backtrace[0];
        $callerLine = $backtrace[1]['class'] . ":" .$backtrace[0]['line'];
        
        echo "Dump called at: " . $callerLine . PHP_EOL;
        foreach ($params as $param)
            var_dump($param);
        exit();
    }

    public static function areTheSameClass($value,
                                           $desiredClass): bool
    {
        return get_class($value) === get_class($desiredClass);
    }

    public static function getParentName(int $offset = 0): string
    {
        $trace = debug_backtrace();

        $class = $trace[1 + $offset]['class'];

        for ($i = 1; $i < count($trace); $i++) {
            if (isset($trace[$i]))
                if ($class != $trace[$i]['class'])
                    return $trace[$i]['class'];
        }

        return '';
    }

    public static function getTracedClass(int $offset = 0): string
    {
        $trace = debug_backtrace();

        return $trace[1 + $offset]['class'];
    }

    public static function getParent(int $offset = 0)
    {
        $trace = debug_backtrace();

        $class = $trace[1 + $offset]['object'];

        for ($i = 1; $i < count($trace); $i++) {
            if (isset($trace[$i]))
                if ($class != $trace[$i]['object'])
                    return $trace[$i]['object'];
        }

        return null;
    }

    /**
     * only a specified class can access method that invokes this function
     * @throws Exception
     */
    public static function restrictToSpecificClassOnly(string $classToRestrictTo = self::class)
    {
        $classRestricted = static::getTracedClass(2);

        if($classRestricted !== $classToRestrictTo)
            throw new Exception("Only $classToRestrictTo class can create new $classRestricted.");

        return null;
    }
}