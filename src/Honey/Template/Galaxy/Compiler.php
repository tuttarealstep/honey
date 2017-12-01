<?php
/**
 * User: tuttarealstep
 * Date: 07/05/17
 * Time: 12.09
 */

namespace Honey\Template\Galaxy;

use DOMDocument;
use Honey\Template\Galaxy\Parser\Parser;

class Compiler
{
    /**
     * @var string
     */
    private $content;

    /**
     * @var array
     */
    private $options;

    private $originalContent;

    private $parser;

    function __construct($content, $options = [])
    {
        $this->content = $content;
        $this->options = $options;

        $this->originalContent = $content;

        $this->parser = new Parser($content, $options);
    }

    /**
     * @param $tag
     * @param null $attributes
     * @param bool $closeTag
     * @param int $space
     * @param $newLine
     * @return string
     */
    private function printTag($tag, $closeTag = false, $attributes = null, $space = 0, $newLine = false)
    {
        $string = "";

        //$string .= str_repeat(' ', $space);

        if($closeTag)
        {
            $string .= "</{$tag}>";
        } else {
            if(!empty($attributes))
            {
                $string .= "<{$tag} {$attributes}>";
            } else {
                $string .= "<{$tag}>";
            }
        }

        if($newLine)
        {
            $string .= "\n";
        }
        return  $string;
    }

    public function compile()
    {
        $tokens = $this->parser->getTokens();

        $graph = new Graph();
        $graph->insertNode(-1);

        $id = 0;
        foreach ($tokens as $token) {
            $type = $token["type"];
            $value = $token["value"];
            $col = $token["column"];
            $line = $token["line"];

            if($graph->getHead()->key == -1)
            {
                $graph->insertNode($id, $token);
                $id++;
                continue;
            }

            $activeTokenNode = $graph->getHead();
            $activeToken = $activeTokenNode->value;


            if ($line == $activeToken["line"] && $col != $activeToken["column"])
            {
                if ($type == 'attributes' && $activeToken['type'] == 'tag') {
                    $tmpHead = $activeTokenNode;
                    $tmpHead->value['attributes'] = $token;

                    $graph->setHead($tmpHead);
                    continue;
                }
            }

            $graph->insertNode($id, $token);

            if ($line > $activeToken["line"] && $col > $activeToken["column"])
            {
                $graph->link($id - 1, $id);
            } elseif ($line > $activeToken["line"] && $col == $activeToken["column"])
            {

                $findId = $id;
                while($col <= $activeTokenNode->value['column'])
                {
                    $findId -= 1;
                    $activeTokenNode = $graph->findNode($findId);
                }

               /* if($col == $activeTokenNode->value['column'])
                {
                    $graph->link($findId, $id);
                }*/

                if($col >= $graph->findNode($findId)->value['column'])
                {
                    $graph->link($findId, $id);
                }
            } elseif ($line > $activeToken["line"] && $col < $activeToken["column"])
            {
                if($type == 'closeHtmlTag')
                {
                   // $graph->link($id - 2, $id);
                    //todo enable it in parser (feature)
                } else {
                    $findId = $id;
                    while ($activeTokenNode->value['type'] != 'tag' || $col <= $activeTokenNode->value['column']) {
                        if(!isset($activeTokenNode->value['type']))
                            break;

                        $findId -= 1;
                        $activeTokenNode = $graph->findNode($findId);
                    }

                    $graph->link($findId, $id);

                }
            } elseif ($line == $activeToken["line"] && $col > $activeToken["column"]) {

                $graph->link($id - 1, $id);
            } else {
                print_r($token);
                die(5);
            }

            $id++;
        }

        $graph->link(-1, 0); //root to the first node
        return $this->beautifyHtml($this->toHTML($graph->findNode(-1), $graph));
    }

    /**
     * @param Node $node
     * @param Graph $graph
     * @return string
     */
    function toHTML($node, $graph)
    {
        $buffer = "";
        $x = &$node;
        //$this->printTest($node, $graph);
        //$graph->showLinks();
        //die();
        do {
            if (!@is_null($x->linkNext)) {
                $k = $x->linkNext->key;
                $activeNode =  $graph->findNode($k);

                $type = $activeNode->value["type"];
                $value = $activeNode->value["value"];
                $col = $activeNode->value["column"];

                $attributes = null;

                if(isset($activeNode->value['attributes']))
                {
                    $attributes = $activeNode->value['attributes']['value'];
                }

                if($type == 'doctype')
                {
                    $tmpBuffer = $buffer;
                    $buffer = '<!DOCTYPE '. $value .'>' . "\n" . $tmpBuffer;
                } else {
                    if($type == 'tag')
                    {
                        $buffer .= $this->printTag($value, false, $attributes, $col - 1, false);
                        $buffer .= $this->toHTML($activeNode, $graph);
                        $buffer .= $this->printTag($value, true, null, $col - 1, false);
                    } else {
                        $buffer .= $value ;
                        //$buffer .= str_repeat(' ', $x->value["column"] + 1) . $value ;
                    }
                }

            }
            $x =& $x->linkNext;
        } while (!@is_null($x) && !@is_null($x->linkNext));

        return $buffer;
    }

    /**
     * @param Node $node
     * @param Graph $graph
     */
    function printTest($node, $graph)
    {
        $x = &$node;
        do {
            if (!@is_null($x->linkNext)) {
                $k = $x->linkNext->key;
                echo str_repeat(' ', $graph->findNode($k)->value['column']) . "--> " . $k . " <-- \n";
                $this->printTest($graph->findNode($k), $graph);
            }
            $x =& $x->linkNext;
        } while (!@is_null($x) && !@is_null($x->linkNext));
    }

    private function beautifyHtml($buffer)
    {
        if (libxml_use_internal_errors(true) === true)
        {
            libxml_clear_errors();
        }

        $dom = new DOMDocument(LIBXML_COMPACT);
        $dom->preserveWhiteSpace = false;
        $dom->loadHTML($buffer);
        $dom->formatOutput = true;
        return $dom->saveHTML();
    }
}