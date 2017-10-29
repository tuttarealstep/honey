<?php
/**
 * User: tuttarealstep
 * Date: 30/06/17
 * Time: 17.26
 */

namespace Honey\Exceptions;

use Exception;
use Throwable;

class HoneyException extends Exception
{
    function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
    
    function errorMessage()
    {
        return 'Error on line '.$this->getLine().' in '.$this->getFile() .': <b>'.$this->getMessage().'</b>';
    }
}