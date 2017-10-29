<?php
/**
 * User: tuttarealstep
 * Date: 06/05/17
 * Time: 13.05
 */

namespace Honey\Helpers\Facades;

class SimpleCache extends Facade
{
    /**
     * @return string
     */
    protected static function getInstance()
    {
        return 'simpleCache';
    }
}