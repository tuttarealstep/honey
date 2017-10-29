<?php
/**
 * User: tuttarealstep
 * Date: 01/05/17
 * Time: 14.57
 */

namespace Honey\Helpers;

use RecursiveArrayIterator;
use RecursiveIteratorIterator;

class ArrayHelper
{
    /**
     * @param array $array
     * @return array
     */
    public static function flatten(array $array)
    {
        return iterator_to_array(new RecursiveIteratorIterator(new RecursiveArrayIterator($array)), 0);
    }

    /**
     * @param $value
     * @return array
     */
    public static function wrap($value)
    {
        return !is_array($value) ? [$value] : $value;
    }

    /**
     * @param $obj
     * @return mixed
     */
    public static function objectToArray($obj)
    {
        return json_decode(json_encode($obj), true);
    }
}