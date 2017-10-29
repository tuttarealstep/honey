<?php
/**
 * User: tuttarealstep
 * Date: 06/05/17
 * Time: 12.49
 */

namespace Honey\Helpers\Facades;

class Router extends Facade
{
    /**
     * @return string
     */
    protected static function getInstance()
    {
        return 'router';
    }
}