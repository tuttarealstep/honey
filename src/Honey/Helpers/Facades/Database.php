<?php
/**
 * User: tuttarealstep
 * Date: 01/05/17
 * Time: 22.36
 */

namespace Honey\Helpers\Facades;

/**
 * @see \Honey\Database\Database
 */
class Database extends Facade
{
    /**
     * @return string
     */
    protected static function getInstance()
    {
        return 'database';
    }
}