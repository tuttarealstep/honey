<?php

namespace Honey\Database;

use Honey\Database\Query\Processors\SqlProcessor;

class SqlDatabase extends Database
{
    protected function getDefaultProcessor()
    {
        return new SqlProcessor;
    }
}