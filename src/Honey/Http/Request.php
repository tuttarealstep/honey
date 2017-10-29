<?php
/**
 * User: tuttarealstep
 * Date: 05/05/17
 * Time: 21.26
 */

namespace Honey\Http;

class Request
{
    /**
     * @return bool
     */
    public static function isPost()
    {
        return ($_SERVER['REQUEST_METHOD'] == 'POST') ? true : false;
    }

    /**
     * @return bool
     */
    public static function isGet()
    {
        return ($_SERVER['REQUEST_METHOD'] == 'GET') ? true : false;
    }

    /**
     * @return bool
     */
    public static function isDelete()
    {
        return ($_SERVER['REQUEST_METHOD'] == 'DELETE') ? true : false;
    }

    /**
     * @return bool
     */
    public static function isPut()
    {
        return ($_SERVER['REQUEST_METHOD'] == 'PUT') ? true : false;
    }

    /**
     * @return bool
     */
    public static function isHead()
    {
        return ($_SERVER['REQUEST_METHOD'] == 'HEAD') ? true : false;
    }

    public static function isAjax()
    {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == "XMLHttpRequest")
        {
            return true;
        }

        return false;
    }

    /**
     * @param bool $float
     * @return mixed
     */
    public static function requestTime($float = false)
    {
        return ($float == true) ? $_SERVER['REQUEST_TIME_FLOAT'] : $_SERVER['REQUEST_TIME'];
    }

    /**
     * @return mixed
     */
    public static function getCompleteUrl()
    {
        return $_SERVER['REQUEST_URI'];
    }

    /**
     * @return mixed
     */
    public static function getScriptName()
    {
        return $_SERVER['SCRIPT_NAME'];
    }

    /**
     * @param bool $jsonDecode
     * @return string
     */
    public static function getFormUrlEncodedData($jsonDecode = false)
    {
        return ($jsonDecode == true) ? json_decode(file_get_contents('php://input')) : file_get_contents('php://input');
    }

    /**
     * @return mixed
     */
    public static function method()
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    public static function getIp()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP']))
        {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
        {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            return $_SERVER['REMOTE_ADDR'];
        }
    }
}