<?php
/**
 * User: tuttarealstep
 * Date: 01/05/17
 * Time: 12.47
 */

namespace Honey\Database\Connectors;

use PDO;

class MySqlConnector extends Connector implements ConnectorInterface
{
    /**
     * @param array $config
     * @return PDO
     */
    public function connect(array $config)
    {
        $dsn = $this->getDsn($config);
        $options = $this->getOptionsWithConfig($config);
        $connection = $this->createConnection($dsn, $config, $options);

        if (!empty($config['database'])) {
            $connection->exec("use `{$config['database']}`;");
        }

        $this->configureEncoding($connection, $config);
        $this->configureTimezone($connection, $config);
        $this->setModes($connection, $config);

        return $connection;
    }

    /**
     * @param PDO $connection
     * @param array $config
     */
    protected function configureTimezone($connection, array $config)
    {
        if (isset($config['timezone'])) {
            $connection->prepare('set time_zone="' . $config['timezone'] . '"')->execute();
        }
    }

    /**
     * @param PDO $connection
     * @param array $config
     * @return mixed
     */
    protected function configureEncoding($connection, array $config)
    {
        if (!isset($config['charset'])) {
            return $connection;
        }

        $connection->prepare("set names '{$config['charset']}'" . $this->getCollation($config))->execute();

        return true;
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
            ? "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']}"
            : "mysql:host={$config['host']};dbname={$config['database']}";
    }


    protected function setModes(PDO $connection, array $config)
    {
        if (isset($config['modes'])) {
            $this->setCustomModes($connection, $config);
        } elseif (isset($config['strict'])) {
            if ($config['strict']) {
                $connection->prepare($this->strictMode())->execute();
            } else {
                $connection->prepare("set session sql_mode='NO_ENGINE_SUBSTITUTION'")->execute();
            }
        }
    }

    /**
     * Set the custom modes on the connection.
     *
     * @param  \PDO $connection
     * @param  array $config
     * @return void
     */
    protected function setCustomModes(PDO $connection, array $config)
    {
        $modes = implode(',', $config['modes']);

        $connection->prepare("set session sql_mode='{$modes}'")->execute();
    }

    /**
     * Get the query to enable strict mode.
     *
     * @return string
     */
    protected function strictMode()
    {
        return "set session sql_mode='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION'";
    }

}