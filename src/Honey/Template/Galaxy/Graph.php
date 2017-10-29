<?php


/**
 * User: tuttarealstep
 * Date: 14/05/17
 * Time: 10.32
 */

namespace Honey\Template\Galaxy;

class Graph
{
    /**
     * @var Node
     */
    private $head;

    function __construct($head = null)
    {
        $this->head = $head;
    }

    /**
     * @param $key
     * @param $value
     */
    public function insertNode($key, $value = null)
    {
        $item = new Node($key, $value);
        $item->next = &$this->head;
        if (!is_null($this->head)) $this->head->prev = &$item;
        $this->head = &$item;
        $this->head->prev = NULL;
    }

    function link($node1, $node2)
    {
        $findNodeResult = &$this->findNode($node1);
        $newLink = new Node($node2);

        if (!is_null($findNodeResult->linkNext))
        {

            while(!is_null($findNodeResult->linkNext))
            {
                $findNodeResult = &$findNodeResult->linkNext;
            };

            $newLink->linkPrev = &$findNodeResult;
            $findNodeResult->linkNext = &$newLink;

            /*
             *  $newLink->linkPrev = &$findNodeResult->linkNext;
            $findNodeResult->linkNext->linkNext = &$newLink;
             */
        } else {
            $findNodeResult->linkNext = &$newLink;
        }


        //if (!is_null($findNodeResult->linkNext)) $findNodeResult->linkNext->linkNext = &$newLink;
        //$newLink->linkPrev = &$findNodeResult->linkNext;
        //$findNodeResult->linkNext = &$newLink;

        /*$newLink->linkNext = &$findNodeResult->linkNext;
        if (!is_null($findNodeResult->linkNext)) $findNodeResult->linkNext->linkPrev = &$newLink;
        $newLink->linkPrev = &$findNodeResult;
        $findNodeResult->linkNext = &$newLink;*/
    }

    function &findNode($key)
    {
        $x =& $this->head;

        while (!is_null($x) && @$x->key != $key) {
            $x =& $x->next;
        }

        return $x;
    }

    function deleteNode($n1)
    {
        $x =& $this->head;
        do {
            if (!@is_null($x)) {
                if ($x->key == $n1) {
                    if (!is_null($x->prev)) $x->prev->next =& $x->next;
                    else $this->head =& $x->next;
                } else {
                    $findLinkResult = &$this->findLink($x->key, $n1);
                    if (!is_null($findLinkResult)) {
                        if (!is_null($findLinkResult->linkPrev))
                        {
                            $findLinkResult->linkPrev->linkNext = &$findLinkResult->linkNext;
                        } else {
                            $findLinkResult = &$findLinkResult->linkNext;
                        }
                    }
                }
            }
            $x =& $x->next;
        } while (!is_null($x));
    }

    function &findLink($node1, $node2)
    {
        $x = &$this->findNode($node1);
        while (!@is_null($x) && @$x->key != $node2) {
            $x =& $x->linkNext;
        }
        return $x;
    }

    /**
     * @return Node|null
     */
    public function getHead()
    {
        return $this->head;
    }

    /**
     * @param Node $head
     */
    public function setHead(Node $head)
    {
        $this->head = $head;
    }

    function showLinks()
    {
        $x =& $this->head;
        do {
            if (!@is_null($x->key)) {
                echo " - node " . $x->key . " : ";
                $a = &$x->linkNext;
                while (!is_null($a)) {
                    echo " -->" . $a->key . "<-- ";
                    $a = &$a->linkNext;
                }
            }
            echo " \n";
            $x = &$x->next;
        } while (!is_null($x));
    }
}