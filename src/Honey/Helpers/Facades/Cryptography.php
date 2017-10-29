<?php
/**
 * User: tuttarealstep
 * Date: 02/05/17
 * Time: 19.01
 */
namespace Honey\Helpers\Facades;

/**
 * @see \Honey\Cryptography\Cryptography
 */
class Cryptography extends Facade
{
    /**
     * @return string
     */
    protected static function getInstance()
    {
        return 'cryptography';
    }
}
