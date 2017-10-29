<?php

namespace Honey\Database\Connectors;

use Exception;
use PDO;

class Connector
{
    protected $options = [
        PDO::ATTR_CASE => PDO::CASE_NATURAL,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_ORACLE_NULLS => PDO::NULL_NATURAL,
        PDO::ATTR_STRINGIFY_FETCHES => false,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ];

    /**
     * @param $dsn
     * @param array $config
     * @param array $options
     * @return PDO
     * @throws Exception
     */
    public function createConnection($dsn, array $config, array $options)
    {
        $username = $config['username'];
        $password = $config['password'];

        try
        {
            return $this->createPdoConnection($dsn, $username, $password, $options);
        } catch (Exception $e) {
            throw new Exception($e);
        }
    }

    /**
     * @param $dsn
     * @param $username
     * @param $password
     * @param $options
     * @return PDO
     */
    protected function createPdoConnection($dsn, $username, $password, $options)
    {
        return new PDO($dsn, $username, $password, $options);
    }

    /**
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @param array $options
     */
    public function setOptions(array $options)
    {
        $this->options = $options;
    }

    /**
     * @param array $config
     * @return array
     */
    public function getOptionsWithConfig(array $config)
    {
        $options = isset($config['options'])? $config['options'] : [];
        return array_diff_key($this->options, $options) + $options;
    }
}