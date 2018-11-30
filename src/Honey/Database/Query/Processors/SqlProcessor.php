<?php
/**
 * User: tuttarealstep
 * Date: 01/05/17
 * Time: 10.35
 */

namespace Honey\Database\Query\Processors;

use Honey\Database\Query\Builder;

class SqlProcessor extends Processor
{
    protected $selectComponents = [
        'aggregate',
        'columns',
        'from',
        'joins',
        'wheres',
        'groups',
        'havings',
        'orders',
        'offset',
        'lock',
    ];

    /**
     * @param Builder $query
     * @return string
     */
    public function compileSelect(Builder $query)
    {
        $original = $query->columns;

        if (is_null($query->columns))
        {
            $query->columns = ['*'];
        }

        $tmpComponents = $this->compileComponents($query);

        $limit = $this->compileLimit($query, $query->limit);

        $tmpExplode = explode(" ", $tmpComponents['columns']);
        array_splice($tmpExplode, 1, 0,$limit);

        $tmpComponents['columns'] = implode(' ', $tmpExplode);

        $sql = trim($this->concatenate($tmpComponents));

        $query->columns = $original;

        return $sql;
    }

    protected function compileLimit(Builder $query, $limit)
    {
        return 'TOP ('. (int) $limit . ')';
    }

    /**
     * @param Builder $query
     * @param $values
     * @return string
     */
    public function compileUpdate(Builder $query, $values)
    {
        $table = $this->wrapTable($query->from);
        $columns = $this->compileUpdateColumns($values);
        $where = $this->compileWheres($query);

        $limit = $this->compileLimit($query, $query->limit);

        $sql = rtrim("update {$limit} {$table} set $columns $where");


        if (!empty($query->orders)) {
            $sql .= ' ' . $this->compileOrders($query, $query->orders);
        }

        return rtrim($sql);
    }



    public function compileDelete(Builder $query)
    {
        $table = $this->wrapTable($query->from);

        $where = is_array($query->wheres) ? $this->compileWheres($query) : '';

        $limit = $this->compileLimit($query, $query->limit);

        $sql = trim("delete {$limit} from {$table} {$where}");

        if (! empty($query->orders)) {
            $sql .= ' '.$this->compileOrders($query, $query->orders);
        }

        return $sql;
    }
}