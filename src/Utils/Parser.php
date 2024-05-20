<?php

namespace App\Utils;

use stdClass;

class Parser
{
    /** convert string in array into single string with desired separators and outcomes
     * examples:
     * arguments: (['a', 'b', 'c', 'd']) returns: a b c d
     * arguments: (['a', 'b', 'c', 'd'], ", ", "", true) returns:  a, b, c, d
     * arguments: (['a', 'b', 'c', 'd'], ', ', ":") returns: a: b, c, d
     * @param string[] $array
     */
    public static function arrayToString(
      array $array,
      string $separator = ' ',
      string $singleSeparator = null,
      bool $singleSeparatorPositionLast = false): string
    {
        if($singleSeparator === null){
            $singleSeparator = $separator;
        }

        $s = '';
        $switch = false;
        $i = 0;
        foreach ($array as $k){
            if($singleSeparatorPositionLast){
                if(++$i === count($array)){
                    $switch = true;
                }
            }
            elseif ($i++ === 2){
                $switch = true;
            }

            $s .= "$k" . ($switch ? $singleSeparator : $separator);
        }
        return $s;
    }
    /** convert string in array into single string with desired separators and outcomes
     * examples:
     * @todo dupsko opis
     * a, b, c, d,
     * a, b, c, d.
     * a. b, c, d,
     * @param string[] $array
     */
    public static function arrayWithKeysToString(array $array,
                                                 string $separatorRecord = ': ',
                                                 string $separatorRow = '; '): string
    {
        $str = '';
        $i = 1;

        foreach ($array as $k => $v){
            $separatorInsideParsed = $separatorRow;
            if($i++ === count($array))
                $separatorInsideParsed = '';

            $str .= $k . $separatorRecord . $v . $separatorInsideParsed;
        }

        return $str;
    }
    
    /** @var array|stdClass|string $val
     * @return  string|stdClass|array
     */
    public static function json($val)
    {
        if(gettype('$val') === 'string'){
            return json_decode($val, true);
        }
        return json_encode($val, JSON_PRETTY_PRINT);
    }

    public static function split(string $string = '', string $splitBy = '.')
    {
        return explode($splitBy, $string);
    }
    public static function getLeftHandOf(string $str = '', string $splitter = '.')
    {
        $a = static::split($str, $splitter);
        return $a[0];
    }

    public static function getRightHandOf(string $str = '', string $splitter = '/')
    {
        $a = static::split($str, $splitter);
        return $a[count($a) - 1];
    }

    public static function floatLimitDecimals($value, int $decimals = 2): string
    {
        return number_format($value, $decimals);
    }

    public static function strStrip(string $subject, string $string = ' '): string
    {
        return str_replace($string, '', $subject);
    }

    /** @param string $template Write string like it should be parsed, <br/>and where the value should be, put <br/><i>__CONTENT__</i>. 'example __CONTENT__on usage' */
    public static function mapAllStringsInArray (array &$array, string $template, bool $mapKeys = false): void
    {
        $a = explode('__CONTENT__', $template);

        $leftHandSide = $a[0];
        $rightHandSide = $a[1];

        foreach ($array as $k => $v) {
            if (!$mapKeys)
                $array[$k] = $leftHandSide . $v . $rightHandSide;
            else {
                unset($array[$k]);
                $array[$leftHandSide . $k . $rightHandSide] = $v;
            }
        }
    }

    public static function arrayValueToKey($needle, array $haystack, bool $strict = false)
    {
        $a = array_search($needle, $haystack, $strict);
        if($a) return $haystack[$a];
        return null;
    }

    public static function iterableToJson(iterable $array, $prettyPrint = false)
    {
        if ($prettyPrint)
            return json_encode($array, JSON_PRETTY_PRINT);
        return json_encode($array);
    }

    /** @return int Count of string replaces. */
    public static function replaceSubstringsInString(array $array, string &$subject): int
    {
        $count = 0;
        foreach ($array as $searchFor => $replaceWith)
            $subject = str_replace($searchFor, $replaceWith, $subject,  $count);
        return $count;
    }

    public static function stripNamespaceFromClass(string $class)
    {
        return static::getRightHandOf($class, '\\');
    }

    public static function replaceClassInNamespace(string $newClass, string $oldClass)
    {
        $classRaw = static::stripNamespaceFromClass($oldClass);
        return rtrim($oldClass, $classRaw) . static::stripNamespaceFromClass($newClass);
    }

    public static function capitalize(string $string): string
    {
        return ucfirst(strtolower($string));
    }

    public static function stackToArrayPath(Stack $stack, $value)
    {
        if($stack->empty())
            return $value;
        $key = $stack->pop();
        $a[$key] = $value;
        return static::stackToArrayPath($stack, $a);
    }

    public static function replaceNamespaceOfClass (string $class, ?string $namespace = null)
    {
        $namespace = $namespace ?? '';

        return $namespace . self::capitalize(self::stripNamespaceFromClass($class));
    }

    public static function amountToString(float $amount): string
    {
        $amount = floor($amount*100)/100;
        return number_format($amount, 2);
    }

    public static function objectToArray($object): array
    {
        $array = [];
        foreach ($object as $keyObj => $valObj) {
            $array[$keyObj] = in_array(
                strtolower(gettype($valObj)), ['object', 'array']
            ) ?
                static::objectToArray($valObj) :
                $valObj;
        }
        return $array;
    }

    public static function arrayToObject(array $data): stdClass
    {
        $obj = new stdClass();
        foreach ($data as $k => $v) {
            if(gettype($v) === 'array')
                $obj->$k = static::arrayToObject($v);
            else $obj->$k = $v;
        }
        return $obj;
    }


    public static function queryToArray(string $rawContent): array
    {
        parse_str($rawContent, $r);
        return$r;
    }

    public static function queryToObject(string $rawContent): stdClass
    {
        $params = explode('&', $rawContent);

        $data = new stdClass();

        foreach ($params as $param) {
            $keyAndValue = explode('=', $param);
            if (isset($keyAndValue[0]) && isset($keyAndValue[1]))
                $data->{$keyAndValue[0]} = $keyAndValue[1];
        }

        return $data;
    }

    public static function hash(string $value, $algo = "sha512"): string
    {
        return hash($algo, $value . Config::yaml()->app->salt);
    }
}