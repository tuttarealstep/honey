<?php
/**
 * User: tuttarealstep
 * Date: 28/04/17
 * Time: 20.09
 */

namespace Honey\Database;

interface DatabaseInterface
{
    /**
     * @param string $table
     * @return mixed
     */
    public function table($table);

    public function select($query, $params = []);

    public function insert($query, $params = []);

    public function delete($query, $params = []);

    public function update($query, $params = []);

    public function statement($query, $params = []);

    public function single($query, $params = []);

    public function rawQuery($query, $params = []);

    public function rollBack();

    public function bind();

    public function bindArray($statement, $params);
}