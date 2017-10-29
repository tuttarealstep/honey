<?php
/**
 * User: tuttarealstep
 * Date: 06/05/17
 * Time: 11.34
 */

namespace Honey\Helpers;

class TimeHelper
{
    /**
     * @param $time
     * @return false|string
     */
    public static function timeNormalFull($time)
    {
        return date('d/m/Y H.i.s', $time);
    }

    /**
     * @param $time
     * @return false|string
     */
    public static function timeNormalHis($time)
    {
        return date('H.i.s', $time);
    }

    /**
     * @param $time
     * @return false|string
     */
    public static function date($time)
    {
        return date('d/m/Y', $time);
    }

}
