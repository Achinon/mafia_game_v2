<?php

namespace App\Utils;

class Preg
{
    public static function base($value)
    {
        if (preg_match("/[a-z,-_]+/", $value))
            return $value;
        return null;
    }

    public static function no_special($value)
    {
        return preg_replace('/[^\p{L}\p{N}]/u', '', $value);
    }

    public static function email($value)
    {
        if (filter_var($value, FILTER_VALIDATE_EMAIL))
            return $value;
        return null;
    }

    public static function url($value)
    {
        if (preg_match("/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i", $value))
            return $value;
        return null;
    }

    public static function nip($value)
    {
        $value = str_replace("-", "", $value);
        $value = str_replace(" ", "", $value);
        if (preg_match("/^\d{10}$/i", $value))
            return $value;
        return null;
    }

    public static function phone($value)
    {
        $value = str_replace(' ', '', $value);
        $value = str_replace('+', '', $value);
        $value = str_replace('-', '', $value);
        $value = str_replace('(', '', $value);
        $value = str_replace(')', '', $value);

        if (preg_match("/^\d{8,16}$/i", $value))
            return $value;
        return null;
    }

    public static function int($value): ?int
    {
        if (preg_match("/^[0-9]*$/", $value))
            return (int)$value;
        return null;
    }

    public static function float($value): ?float
    {
        if(is_numeric($value))
            return (float)$value;
        return null;
    }

    public static function array(array $array, string $pregFunctionName): array
    {
        if(method_exists(self::class, $pregFunctionName))
            foreach ($array as $k => $v)
                $array[$k] = Preg::$pregFunctionName($v);
        return $array;
    }
    
    public static function password (string $password): ?string {
        if (strlen($password) >= 9 &&
          preg_match('@[A-Z]@', $password) &&
          preg_match('@[a-z]@', $password) &&
          preg_match('@[0-9]@', $password) &&
          preg_match('@[\W]@', $password)) {
            return $password;
        }
        return null;
    }
    
    public static function ascii(mixed $betID)
    {
    
    }
}