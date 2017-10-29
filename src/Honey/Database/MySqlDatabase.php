<?php
/**
 * User: tuttarealstep
 * Date: 01/05/17
 * Time: 12.39
 */

namespace Honey\Database;

use Honey\Database\Query\Processors\MySqlProcessor;

class MySqlDatabase extends Database
{
    protected function getDefaultProcessor()
    {
        return new MySqlProcessor;
    }
}