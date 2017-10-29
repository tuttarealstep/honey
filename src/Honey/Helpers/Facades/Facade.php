<?php
/**
 * User: tuttarealstep
 * Date: 01/05/17
 * Time: 22.40
 */

namespace Honey\Helpers\Facades;

use RuntimeException;

class Facade
{
    protected static $app;
    protected static $resolvedInstance;

    protected static function getRootInstance()
    {
        return static::resolveFacadeInstance(static::getInstance());
    }

    /**
     * @return string
     * @throws \RuntimeException
     */
    protected static function getInstance()
    {
        throw new RuntimeException('Facade does not implement getInstance method.');
    }

    /**
     * @param $name
     * @return mixed
     */
    protected static function resolveFacadeInstance($name)
    {
        if (is_object($name))
        {
            return $name;
        }

        if (isset(static::$resolvedInstance[$name]))
        {
            return static::$resolvedInstance[$name];
        }

        if(is_array(static::$app))
        {
            return static::$resolvedInstance[$name] = static::$app[$name];
        }

        return static::$resolvedInstance[$name] = static::$app->$name;
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public static function __callStatic($name, $arguments)
    {
        $instance = static::getRootInstance();

        if(!$instance)
        {
            throw new RuntimeException('A Facade has not been set.');
        }

        return $instance->$name(...$arguments);
    }

    /**
     * @return mixed
     */
    public static function getApp()
    {
        return static::$app;
    }

    /**
     * @param mixed $app
     */
    public static function setApp($app)
    {
        static::$app = $app;
    }
}