<?php
/**
 * User: tuttarealstep
 * Date: 01/05/17
 * Time: 10.36
 */

namespace Honey\Database\Query\Processors;

use Honey\Database\Query\Builder;
use Honey\Helpers\ArrayHelper;

class Processor
{
    protected $operators = [];

    protected $selectComponents = [
        'aggregate',
        'columns',
        'from',
        'joins',
        'wheres',
        'groups',
        'havings',
        'orders',
        'limit',
        'offset',
        'lock',
    ];

    protected $tablePrefix = '';

    public function getOperators()
    {
        return $this->operators;
    }

    /**
     * @param array $operators
     */
    public function setOperators(array $operators)
    {
        $this->operators = $operators;
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

        $sql = rtrim("update {$table} set $columns $where");


        if (!empty($query->orders)) {
            $sql .= ' ' . $this->compileOrders($query, $query->orders);
        }

        if (isset($query->limit)) {
            $sql .= ' ' . $this->compileLimit($query, $query->limit);
        }

        return rtrim($sql);
    }

    /**
     * @param $table
     * @return string
     */
    public function wrapTable($table)
    {
        return $this->tablePrefix . $table;
    }

    /**
     * @param $values
     * @return string
     */
    protected function compileUpdateColumns($values)
    {
        $returnValues = [];

        foreach ($values as $key => $value) {
            $returnValues[] = $key . ' = ?';
        }

        return implode(',', $returnValues);
    }

    /**
     * @param Builder $query
     * @return string
     */
    protected function compileWheres(Builder $query)
    {

        if (is_null($query->wheres)) {
            return '';
        }

        if (count($sql = $this->compileWheresToArray($query)) > 0) {
            return $this->concatenateWhereClauses($sql);
        }

        return '';
    }

    /**
     * @param $query
     * @return array
     */
    protected function compileWheresToArray($query)
    {
        $whereReturn = [];
        foreach ($query->wheres as $where) {
            $whereReturn[] = $where['boolean'] . ' ' . $this->getWhere($where);
        }

        return $whereReturn;
    }

    /**
     * @param $where
     * @return string
     */
    protected function getWhere($where)
    {
        return $where['column'] . ' ' . $where['operator'] . ' ?';
    }

    /**
     * @param $sql
     * @return string
     */
    protected function concatenateWhereClauses($sql)
    {
        return 'where ' . $this->removeLeadingBoolean(implode(' ', $sql));
    }

    /**
     * @param $value
     * @return mixed
     */
    protected function removeLeadingBoolean($value)
    {
        return preg_replace('/and |or /i', '', $value, 1);
    }

    /**
     * @param Builder $query
     * @param $orders
     * @return string
     */
    protected function compileOrders(Builder $query, $orders)
    {
        if (!empty($orders)) {
            return 'order by ' . implode(', ', $this->compileOrdersToArray($orders));
        }

        return '';
    }

    /**
     * @param $orders
     * @return array
     */
    protected function compileOrdersToArray($orders)
    {
        return array_map(function ($order) {
            return !isset($order['sql']) ? $order['column'] . ' ' . $order['direction'] : $order['sql'];
        }, $orders);
    }

    /**
     * @param Builder $query
     * @param $limit
     * @return string
     */
    protected function compileLimit(Builder $query, $limit)
    {
        return 'limit '. (int) $limit;
    }

    /**
     * @return string
     */
    public function getTablePrefix(): string
    {
        return $this->tablePrefix;
    }

    /**
     * @param string $tablePrefix
     */
    public function setTablePrefix(string $tablePrefix)
    {
        $this->tablePrefix = $tablePrefix;
    }

    /**
     * @param array $bindings
     * @param array $values
     * @return mixed
     */
    public function prepareBindingsForUpdate(array $bindings, array $values)
    {
        $bindingsWithoutJoin = $bindings;
        unset($bindingsWithoutJoin['join']);

        return array_values(
            array_merge($bindings['join'], $values, ArrayHelper::flatten($bindingsWithoutJoin))
        );
    }

    public function getDateFormat()
    {
        return 'Y-m-d H:i:s';
    }

    public function compileDelete(Builder $query)
    {
        $table = $this->wrapTable($query->from);

        $where = is_array($query->wheres) ? $this->compileWheres($query) : '';

        $sql = trim("delete from {$table} {$where}");

        if (! empty($query->orders)) {
            $sql .= ' '.$this->compileOrders($query, $query->orders);
        }

        if (isset($query->limit)) {
            $sql .= ' '.$this->compileLimit($query, $query->limit);
        }

        return $sql;
    }

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

        $sql = trim($this->concatenate($this->compileComponents($query)));

        $query->columns = $original;

        return $sql;
    }

    protected function compileComponents(Builder $query)
    {
        $sql = [];

        foreach ($this->selectComponents as $component)
        {
            if (!is_null($query->$component))
            {
                $method = 'compile'.ucfirst($component);
                $sql[$component] = $this->$method($query, $query->$component);
            }
        }

        return $sql;
    }

    /**
     * @param Builder $query
     * @param $aggregate
     * @return string
     */
    protected function compileAggregate(Builder $query, $aggregate)
    {
        $column = implode(', ', $aggregate['columns']);

        if ($query->distinct && $column !== '*')
        {
            $column = 'distinct '. $column;
        }

        return 'select ' . $aggregate['function'] . '('.$column.') as aggregate';
    }

    /**
     * @param Builder $query
     * @param $columns
     * @return string | null
     */
    protected function compileColumns(Builder $query, $columns)
    {
        if (!is_null($query->aggregate)) {
            return null;
        }

        $select = $query->distinct ? 'select distinct ' : 'select ';

        return $select . implode(', ', $columns);
    }

    /**
     * @param Builder $query
     * @param $table
     * @return string
     */
    protected function compileFrom(Builder $query, $table)
    {
        return 'from ' . $this->wrapTable($table);
    }

    /**
     * @param $segments
     * @return string
     */
    protected function concatenate($segments)
    {
        return implode(' ', array_filter($segments, function ($value) {return (string) $value !== '';}));
    }

    public function compileInsert(Builder $query, array $values)
    {
        $table = $this->wrapTable($query->from);

        if (! is_array(reset($values)))
        {
            $values = [$values];
        }

        $columns = implode(', ', array_keys(reset($values)));

        $parameters = [];
        foreach ($values as $record)
        {
            $parameters[] = '('.implode(', ', array_map(function () { return '?'; }, $record)) .')';
        }

        $parameters = implode(', ', $parameters);

        return "insert into $table ($columns) values $parameters";
    }

}