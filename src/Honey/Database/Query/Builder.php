<?php
/**
 * User: tuttarealstep
 * Date: 30/04/17
 * Time: 17.15
 */

namespace Honey\Database\Query;

use Honey\Database\Database;
use Honey\Database\Query\Processors\Processor;
use Honey\Helpers\ArrayHelper;
use InvalidArgumentException;

class Builder
{
    /**
     * @var Database
     */
    public $database;

    /**
     * @var
     */
    public $processor;

    /**
     * @var array
     */
    public $columns = ["*"];

    /**
     * @var string
     */
    public $from;

    /**
     * @var array
     */
    public $wheres = [];

    /**
     * @var array
     */
    public $orders;

    /**
     * @var int
     */
    public $limit;

    /**
     * @var array
     */
    public $aggregate;

    /**
     * @var bool
     */
    public $distinct = false;

    /**
     * @var array
     */
    public $operators = [
        '=', '<', '>', '<=', '>=', '<>', '!=',
        'like', 'like binary', 'not like', 'between', 'ilike',
        '&', '|', '^', '<<', '>>',
        'rlike', 'regexp', 'not regexp',
        '~', '~*', '!~', '!~*', 'similar to',
        'not similar to', 'not ilike', '~~*', '!~~*',
    ];

    /**
     * @var array
     */
    public $joins;

    /**
     * @var array
     */
    public $groups;

    /**
     * @var array
     */
    public $havings;

    /**
     * @var int
     */
    public $offset;

    /**
     * @var string|bool
     */
    public $lock;

    public $bindings = [
        'select' => [],
        'join'   => [],
        'where'  => [],
        'having' => [],
        'order'  => [],
        'union'  => [],
    ];

    public function __construct(Database $database, Processor $processor)
    {
        $this->database = $database;
        $this->processor = $processor;
    }

    public function select($columns = ['*'])
    {
        $original = $this->columns;

        if (!is_null($original))
        {
            $this->columns = $columns;
        }

        $results = $this->runSelect();//$this->runSelect();

        $this->columns = $original;

        return $results;
    }

    public function get($columns = ['*'])
    {
       return $this->select($columns);
    }

    /**
     * @param array $columns
     * @return $this
     */
    public function runGet($columns = ["*"])
    {
        $this->columns = is_array($columns) ? $columns : func_get_args();

        return $this;
    }

    protected function runSelect()
    {
        return $this->database->select($this->toSql(), $this->getBindings());
    }

    public function insert(array $values)
    {
        if (empty($values))
        {
            return true;
        }

        if (!is_array(reset($values)))
        {
            $values = [$values];
        } else {
            foreach ($values as $key => $value)
            {
                ksort($value);
                $values[$key] = $value;
            }
        }

        return $this->database->insert($this->processor->compileInsert($this, $values), array_values(ArrayHelper::flatten($values)));
    }

    public function toSql()
    {
        return $this->processor->compileSelect($this);
    }

    /**
     * The table name which the query is targeting
     * @param string $table
     * @return $this
     */
    public function from($table)
    {
        $this->from = $table;

        return $this;
    }

    public function delete($key = null, $value = null)
    {
        if (!is_null($key) && !is_null($value))
        {
            $this->where($this->from . '.' . $key, '=', $value);
        }

        return $this->database->delete($this->processor->compileDelete($this), $this->getBindings());
    }

    public function where($column, string $operator = null, string $value = null, string $boolean = "and")
    {
        if (is_array($column))
        {
            $_this = null;
            foreach ($column as $whereItem)
            {
                $_this = call_user_func_array([$this, "where"], $whereItem);
            }

            return $_this;
        }

        if (!is_array($column) && $operator == null)
        {
            throw new \RuntimeException("The column parameter need to be an array if the operator parameter is null!");
        }

        if(empty($value))
        {
            $value = $operator;
            $operator = "=";
        }

        list($value, $operator) = $this->prepareValueAndOperator($value, $operator);


        $this->wheres[] = compact(
            'column', 'operator', 'value', 'boolean'
        );

        $this->addBinding($value, 'where');

        return $this;
    }

    public function update(array $values)
    {
        $sql = $this->processor->compileUpdate($this, $values);
        return $this->database->update($sql, $this->processor->prepareBindingsForUpdate($this->bindings, $values));
    }

    /**
     * @param $column
     * @return Builder
     */
    public function orderByDesc($column)
    {
        return $this->orderBy($column, 'desc');
    }

    /**
     * @param $value
     * @param $operator
     * @return array
     */
    public function prepareValueAndOperator($value, $operator)
    {
        if ($this->invalidOperatorAndValue($value, $operator)) {
            throw new InvalidArgumentException('Illegal operator and value combination.');
        }

        return [$value, $operator];
    }

    /**
     * @param $column
     * @param string $direction
     * @return $this
     */
    public function orderBy($column, $direction = 'asc')
    {
        $this->orders[] = [
            'column' => $column,
            'direction' => strtolower($direction) == 'asc' ? 'asc' : 'desc',
        ];

        return $this;
    }

    /**
     * @param int $value
     * @return $this
     */
    public function limit($value)
    {
        if ($value >= 0) {
            $this->limit = $value;
        }

        return $this;
    }

    /**
     * @param $operator
     * @param $value
     * @return bool
     */
    protected function invalidOperatorAndValue($operator, $value)
    {
        return is_null($value) && in_array($operator, $this->operators) && !in_array($operator, ['=', '<>', '!=']);
    }

    /**
     * @param $value
     * @param string $type
     * @return $this
     */
    public function addBinding($value, $type = 'where')
    {
        if (!array_key_exists($type, $this->bindings)) {
            throw new InvalidArgumentException("Invalid binding type: {$type}.");
        }

        if (is_array($value)) {
            $this->bindings[ $type ] = array_values(array_merge($this->bindings[ $type ], $value));
        } else {
            $this->bindings[ $type ][] = $value;
        }

        return $this;
    }

    /**
     * @param $operator
     * @return bool
     */
    protected function invalidOperator($operator)
    {
        return !in_array(strtolower($operator), $this->operators, true) && !in_array(strtolower($operator), $this->processor->getOperators(), true);
    }

    public function getBindings()
    {
        return ArrayHelper::flatten($this->bindings);
    }

    /**
     * @param $function
     * @param array $columns
     * @return mixed
     */
    public function aggregate($function, $columns = ['*'])
    {
        $results = $this->cloneWithout(['columns'])
            ->cloneWithoutBindings(['select'])
            ->setAggregate($function, $columns)
            ->get($columns);

        if (!empty($results)) {
            return array_change_key_case((array) $results[0])['aggregate'];
        }

        return [];
    }

    /**
     * @param $function
     * @param $columns
     * @return $this
     */
    protected function setAggregate($function, $columns)
    {
        $this->aggregate = compact('function', 'columns');

        return $this;
    }


    /**
     * @param $value
     * @param $callback
     * @return $this|Builder
     */
    public function when($value, $callback)
    {
        if ($value) {
            return $callback($this, $value) ?: $this;
        }

        return $this;
    }


    public function tap($callback)
    {
        return $this->when(true, $callback);
    }

    /**
     * @param array $except
     * @return mixed
     */
    public function cloneWithout(array $except)
    {
        return $this->tap(function ($clone) use ($except) {
            foreach ($except as $property) {
                $clone->{$property} = null;
            }
        });
    }

    /**
     * @param array $except
     * @return mixed
     */
    public function cloneWithoutBindings(array $except)
    {
        return $this->tap(function ($clone) use ($except) {
            foreach ($except as $type) {
                $clone->bindings[$type] = [];
            }
        });
    }

    /**
     * @param array $columns
     * @return mixed
     */
    public function first($columns = ['*'])
    {
        $columns = ArrayHelper::wrap($columns);
        $elements = $this->limit(1)->get($columns);
        if(count($elements) >= 0 && isset($elements[0]))
        {
            return $elements[0];
        }

        return false;
    }

    /**
     * @param  string  $column
     * @return mixed
     */
    public function value($column)
    {
        $column = ArrayHelper::wrap($column);
        $first = $this->first($column);
        if($first != false)
        {
            $result = (array)$first;
            return $result[$column];
        } else {
            return false;
        }
    }

    /**
     * @param string $columns
     * @return int
     */
    public function count($columns = '*')
    {
        return (int) $this->aggregate(__FUNCTION__, ArrayHelper::wrap($columns));
    }

    /**
     * @param $column
     * @return mixed
     */
    public function min($column)
    {
        return $this->aggregate(__FUNCTION__, [$column]);
    }

    /**
     * @param  string  $column
     * @return mixed
     */
    public function max($column)
    {
        return $this->aggregate(__FUNCTION__, [$column]);
    }
}