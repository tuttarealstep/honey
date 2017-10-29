<?php
/**
 * User: tuttarealstep
 * Date: 13/05/17
 * Time: 21.37
 */

namespace Honey\Template\Galaxy;

class Node
{
    public $key;
    public $value;
    /**
     * @var Node
     */
    public $next;

    /**
     * @var Node
     */
    public $prev;
    public $linkPrev;
    public $linkNext;

    function __construct($key, $value = null, $next = null, $prev = null, $linkPrev = null, $linkNext = null)
    {
        $this->key = $key;
        $this->value = $value;
        $this->next = $next;
        $this->prev = $prev;
        $this->linkPrev = $linkPrev;
        $this->linkNext = $linkNext;
    }
}