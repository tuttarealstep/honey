<?php
/**
 * User: tuttarealstep
 * Date: 02/05/17
 * Time: 17.07
 */

namespace Honey\Standards;

interface Application
{
    /**
     * @return string
     */
    public function version();

    public function run();

    public function initialize();
}