<?php
/**
 * User: tuttarealstep
 * Date: 28/04/17
 * Time: 20.08
 */

namespace Honey\Database;

use Closure;
use DateTimeInterface;
use Exception;
use Honey\Database\Query\Builder;
use Honey\Database\Query\Processors\Processor;
use PDO;
use PDOStatement;

class Database implements DatabaseInterface
{
    //TODO implements other sql helper and functions
    /**
     * @var PDO
     */
    protected $pdo;

    /**
     * @var string
     */
    protected $database = '';

    /**
     * @var string
     */
    protected $tablePrefix = '';

    /**
     * @var int
     */
    protected $transactions = 0;

    /**
     * @var Processor
     */
    protected $processor;

    /**
     * @var int
     */
    protected $fetchMode = PDO::FETCH_OBJ;

    /**
     * Database constructor.
     * @param $pdo
     * @param string $database
     * @param string $tablePrefix
     */
    public function __construct($pdo, string $database = '', string $tablePrefix = '')
    {
        $this->pdo = $pdo;
        $this->database = $database;
        $this->tablePrefix = $tablePrefix;

        $this->useDefaultProcessor();
    }

    public function table($table)
    {
        return $this->query()->from($table);
    }

    /**
     * @param $query
     * @param array $params
     * @return mixed
     * @throws Exception
     */
    public function select($query, $params = [])
    {
        return $this->run($query, $params, function ($query, $bindings)
        {
            $statement = $this->prepared($this->getPdo()->prepare($query));
            $this->bindArray($statement, $this->prepareBindings($bindings));
            $statement->execute();
            return $statement->fetchAll();
        });
    }

    /**
     * @param $query
     * @param array $params
     * @return mixed
     * @throws Exception
     */
    public function insert($query, $params = [])
    {
        return $this->statement($query, $params);
    }

    /**
     * @param $query
     * @param array $params
     * @return mixed
     * @throws Exception
     */
    public function delete($query, $params = [])
    {
        return $this->countStatement($query, $params);
    }

    /**
     * @param $query
     * @param array $params
     * @return mixed
     * @throws Exception
     */
    public function update($query, $params = [])
    {
        return $this->countStatement($query, $params);
    }

    /**
     * @param $query
     * @param array $params
     * @return mixed
     * @throws Exception
     */
    public function statement($query, $params = [])
    {
        return $this->run($query, $params, function ($query, $bindings)
        {
            $statement = $this->getPdo()->prepare($query);
            $this->bindArray($statement, $this->prepareBindings($bindings));
            return $statement->execute();
        });
    }

    /**
     * @param $query
     * @param array $params
     * @return mixed
     * @throws Exception
     */
    public function countStatement($query, $params = [])
    {
        return $this->run($query, $params, function ($query, $params) {
            $statement = $this->getPdo()->prepare($query);
            $this->bindArray($statement, $this->prepareBindings($params));
            $statement->execute();

            return $statement->rowCount();
        });
    }

    /**
     * @param $query
     * @param array $params
     * @return mixed
     */
    public function single($query, $params = [])
    {
        return array_shift($this->select($query, $params));
    }

    /**
     * @param $query
     * @param array $params
     * @return mixed
     * @throws Exception
     */
    public function rawQuery($query, $params = [])
    {
        return $this->run($query, $params, function ($query, $params)
        {
            $query = trim(str_replace("\r", " ", $query));
            $rawStatement = explode(" ", preg_replace("/\\s+|\t+|\n+/", " ", $query));

            $statement = strtolower($rawStatement[0]);
            if ($statement === 'select' || $statement === 'show')
            {
                $statement = $this->prepared($this->getPdo()->prepare($query));
                $this->bindArray($statement, $this->prepareBindings($params));
                $statement->execute();
                return $statement->fetchAll();
            } elseif ($statement === 'insert' || $statement === 'update' || $statement === 'delete')
            {
                $statement = $this->getPdo()->prepare($query);
                $this->bindArray($statement, $this->prepareBindings($params));
                $statement->execute();
                return $statement->rowCount();
            } else {
                return null;
            }
        });
    }

    public function rollBack()
    {
       /* if (--$this->transactions) {
            $this->getPdo()->exec('ROLLBACK TRAN trans'. ($this->transactions + 1));
            return true;
        }*/
        return  $this->getPdo()->rollback();
        //return $this->getPdo()->rollBack();
    }

    public function lastInsertId()
    {
        return $this->getPdo()->lastInsertId();
    }

    public function bind()
    {
        // TODO: Implement bind() method.
    }

    /**
     * @param \PDOStatement $statement
     * @param $params
     */
    public function bindArray($statement, $params)
    {
        foreach ($params as $key => $value) {
            $statement->bindValue(
                is_string($key) ? $key : $key + 1, $value,
                is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR
            );
        }
    }

    public function query()
    {
        return new Builder($this, $this->getProcessor());
    }

    public function disconnect()
    {
        $this->setPdo(null);
    }

    /**
     * @return Processor
     */
    public function getProcessor(): Processor
    {
        return $this->processor;
    }

    /**
     * @param PDO $pdo
     * @return $this
     */
    public function setPdo(PDO $pdo)
    {
        $this->transactions = 0;
        $this->pdo = $pdo;

        return $this;
    }

    /**
     * @return PDO
     */
    public function getPdo(): PDO
    {
        return $this->pdo;
    }

    /**
     * @param array $params
     * @return array
     */
    public function prepareBindings(array $params)
    {
        $processor = $this->getProcessor();
        foreach ($params as $key => $value) {
            if ($value instanceof DateTimeInterface) {
                $bindings[ $key ] = $value->format($processor->getDateFormat());
            } elseif ($value === false) {
                $bindings[ $key ] = 0;
            }
        }

        return $params;
    }

    /**
     * @param $query
     * @param $params
     * @param Closure $callback
     * @return mixed
     * @throws Exception
     */
    protected function run($query, $params, Closure $callback)
    {
        try {
            $result = $this->runQueryCallback($query, $params, $callback);
        } catch (Exception $e) {
            throw new Exception($e);
        }

        //todo log and handle

        return $result;
    }

    /**
     * @param $query
     * @param $params
     * @param Closure $callback
     * @return mixed
     * @throws Exception
     */
    protected function runQueryCallback($query, $params, Closure $callback)
    {
        try {
            $result = $callback($query, $params);
        } catch (Exception $e) {
            throw new Exception($e);
        }

        //todo handle

        return $result;
    }

    /**
     * @param Processor $processor
     */
    public function setProcessor(Processor $processor)
    {
        $this->processor = $processor;
    }

    public function useDefaultProcessor()
    {
        $this->processor = $this->getDefaultProcessor();
    }

    protected function getDefaultProcessor()
    {
        return new Processor();
    }

    /**
     * @param PDOStatement $statement
     * @return PDOStatement
     */
    protected function prepared(PDOStatement $statement)
    {
        $statement->setFetchMode($this->fetchMode);
        return $statement;
    }

    /**
     * @return bool
     */
    public function beginTransaction()
    {
       // if (!$this->transactions++)
       // {
            return $this->getPdo()->beginTransaction();
        //}

       /* $this->getPdo()->exec('SAVEPOINT trans' . $this->transactions);
        return $this->transactions >= 0;*/
    }

    /**
     * @return bool
     */
    public function commit()
    {
      //  if (!--$this->transactions) {
            return $this->getPdo()->commit();
       /* }
        return $this->transactions >= 0;*/
    }


}