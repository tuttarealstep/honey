<?php
/**
 * User: tuttarealstep
 * Date: 30/06/17
 * Time: 17.26
 */

namespace Honey\Exceptions;

use Exception;
use Throwable;

class DebugException extends Exception
{
    function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    function errorMessage()
    {
        $error = <<<ERROR
<html>
    <head>
        <title>Exception:</title>
        <style>
            html, body{
                margin: 0;
                padding: 0;
            }

            body
            {
                background-color: #fff;
                padding: 10px;
                color: #212121;
            }

            .container
            {
                width: 80%;
                margin: auto;
            }

            .box
            {
                display: inline-block;
                border: 1px solid rgba(0, 0, 0, 0.4);
                border-radius: 4px;
                padding: 4px;
                margin-bottom: 10px;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="box" style="width: 100%">
                Error on line $this->getLine() in $this->getFile(): <b> $this->getMessage() </b>
            </div>
        </div>
        <div class="container">
            <div class="box">
                asdsad
            </div>
            <div class="box">
                asdsad
            </div>
        </div>
    </body>
</html>
ERROR;

        return $error;
        //return 'Error on line '.$this->getLine().' in '.$this->getFile() .': <b>'.$this->getMessage().'</b>';
    }
}