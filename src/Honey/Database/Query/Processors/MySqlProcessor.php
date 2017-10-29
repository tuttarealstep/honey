<?php
/**
 * User: tuttarealstep
 * Date: 01/05/17
 * Time: 10.35
 */

namespace Honey\Database\Query\Processors;

use Honey\Database\Query\Builder;

class MySqlProcessor extends Processor
{
    public function compileSelect(Builder $query)
    {
        $sql = parent::compileSelect($query);

        //todo unions

        return $sql;
    }
}