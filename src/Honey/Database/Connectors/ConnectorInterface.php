<?php
/**
 * User: tuttarealstep
 * Date: 01/05/17
 * Time: 12.50
 */

namespace Honey\Database\Connectors;

interface ConnectorInterface
{
    /**
     * @param array $config
     */
    public function connect(array $config);
}