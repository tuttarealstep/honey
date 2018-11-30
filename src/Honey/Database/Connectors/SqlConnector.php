<?php
/**
 * User: tuttarealstep
 * Date: 01/05/17
 * Time: 12.47
 */

namespace Honey\Database\Connectors;

use PDO;

class SqlConnector extends Connector implements ConnectorInterface
{
    /**
     * @param array $config
     * @return PDO
     * @throws \Exception
     */
    public function connect(array $config)
    {
        $dsn = $this->getDsn($config);

        unset($this->options[PDO::ATTR_EMULATE_PREPARES]);

        $options = $this->getOptionsWithConfig($config);
        $connection = $this->createConnection($dsn, $config, $options);

        if (!empty($config['database'])) {
            $connection->exec("use {$config['database']};");
        }

        return $connection;
    }

    /**
     * @param array $config
     * @return string
     */
    protected function getCollation(array $config)
    {
        if(!isset($config['collation']))
            return '';

        return !is_null($config['collation']) ? " collate '{$config['collation']}'" : '';
    }

    protected function getDsn(array $config)
    {
        return isset($config['port'])
            ? "sqlsrv:server={$config['host']},{$config['port']};Database={$config['database']}"
            : "sqlsrv:server={$config['host']};Database={$config['database']}";
    }
}