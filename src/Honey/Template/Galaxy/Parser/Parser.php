<?php
/**
 * User: tuttarealstep
 * Date: 11/05/17
 * Time: 18.10
 */

namespace Honey\Template\Galaxy\Parser;

class Parser
{
    private $input;

    private $options;

    private $tokens = [];

    private $endOfFile = false;

    private $inputLength;

    private $inputLengthTmp = -1;

    private $lineCount = 1;

    private $colCount = 1;

    private $indentRegex;

    function __construct($content, $options = [])
    {
        $this->options = $options;
        $this->input = preg_replace("/\r\n|\r/", "\n", $content);
    }

    private function isEoF()
    {
        $this->inputLength = strlen($this->input);

        if($this->inputLengthTmp == -1)
        {
            $this->inputLengthTmp = $this->inputLength;
            return false;
        }

        if($this->inputLengthTmp == $this->inputLength)
        {
            $this->endOfFile = true;
            return true;
        } elseif ($this->inputLengthTmp > $this->inputLength)
        {
            $this->inputLengthTmp = -1;
            return false;
        }

        if((bool) strlen($this->input))
        {
            return false;
        }

        $this->endOfFile = true;

        return true;
    }

    private function incrementColumn($increment)
    {
        $this->colCount += $increment;
    }

    private function incrementLine($increment = 1)
    {
        $this->lineCount += $increment;

        if($increment)
        {
            $this->colCount = 1;
        }
    }

    private function consume($length)
    {
        $this->input = substr($this->input, $length);
    }

    private function isTag()
    {
        if (preg_match('/^(\w[-:\w]*)(\/?)/', $this->input, $matches))
        {
            $this->consume(strlen($matches[0]));
            $name = $matches[1];
            $this->tokens[] = $this->generateToken('tag', $name);
            $this->incrementColumn(strlen($matches[0]));
            return true;
        }
    }

    private function scanIndent()
    {
        /*
         * Tab
         */
        $indentRegex = '/^(\t*)/';
        preg_match($indentRegex, $this->input, $captures);

        /*
         * Space
         */
        if($captures && !strlen($captures[1]))
        {
            $indentRegex = '/^( *)/';
            preg_match($indentRegex, $this->input, $captures);
        }

        if($captures && strlen($captures[1]))
        {
            $this->indentRegex = $indentRegex;
        }

        return $captures;
    }

    private function isIndent()
    {
        $captures = $this->scanIndent();

        if($captures)
        {
            $indents = strlen($captures[1]);
            $this->consume($indents);
            $this->incrementColumn($indents);
            return true;
        }
    }

    private function isBlank()
    {
        preg_match('/^(\n)/', $this->input, $captures);

        if($captures)
        {
            $this->generateToken("newLine");
            $this->consume(strlen($captures[1]));
            $this->incrementLine();

            return true;
        }
    }

    private function isHtml()
    {
        //ANY (<[^>]*>)(.*)(<\/[^>]*>)|(<[^>]*>)
        //
        //preg_match('/(<([^>]*) [^>]*>)(.*)(<\/[^>]*>)|(<([^>]*) [^>]*>)|(<([^>]*)[^>]*>)(.*)(<\/[^>]*>)|(<([^>]*)[^>]*>)/', $this->input, $captures);

        //match open html tag
        preg_match('/^(<([a-zA-Z0-9]*)( [^>]*)*>)/', $this->input, $captures);

        if($captures)
        {
            $components["tag"] = $captures[2];

            if(isset($captures[3]))
            {
                $components["attributes"] = $captures[3];
            }

            $this->tokens[] = $this->generateToken('openHtmlTag', $components);
            $this->consume(strlen($captures[1]));
            $this->incrementColumn(strlen($captures[1]));

            $buffer = "";

            while(!preg_match('/^(<\/'.$captures[2].'[^>]*>)/', $this->input))
            {
                $buffer .= $this->input[0];
                $this->consume(strlen($this->input[0]));
            }

            $this->consume(strlen($captures[2]) + 3);

            $this->tokens[] = $this->generateToken('innerHtmlTag', $buffer);

            for($i = 0; $i < count(explode("\n", $buffer)); $i++)
            {
                $this->incrementLine();
            }

            $this->tokens[] = $this->generateToken('closeHtmlTag', $captures[2]);
            //**

            return true;
            //print_r($this->input);
        } else {
            //TODO DELETE OR CHECK //DEPRECATED
            //Close html tag
            preg_match('/^(<\/([a-zA-Z0-9]*)[^>]*>)/', $this->input, $captures);

            if($captures)
            {
                $this->tokens[] = $this->generateToken('closeHtmlTag', $captures[2]);
                $this->consume(strlen($captures[1]));
                $this->incrementColumn(strlen($captures[1]));
                return true;
            }
        }
    }

    private function isAttribute()
    {
        if(isset($this->input[0]) && $this->input[0] == '(')
        {
            preg_match('/^(\((.*)\))/', $this->input, $captures);
            if($captures)
            {
                $this->consume(strlen($captures[1]));
                $this->tokens[] = $this->generateToken('attributes', $captures[2]);
                $this->incrementColumn(strlen($captures[1]));
                return true;
            }
        }
    }

    private function isText()
    {
        if(isset($this->input[0]) && ($this->input[0] == '.' || $this->input[0] == ','))
        {
            preg_match('/^,,((.|\n)*?),,/', $this->input, $captures);
            if($captures)
            {
                $count = 1;
                if(isset($captures[2]))
                {
                    $count = 6;
                }


                /*$checkMultiplicity = explode(",,", $captures[1]);
                if(count($checkMultiplicity) > 1)
                {
                    $this->consume(strlen($checkMultiplicity[0]) + $count);
                    $this->tokens[] = $this->generateToken('text', $checkMultiplicity[0]);
                    $this->incrementLine(count(explode("\n", $checkMultiplicity[0])) - 1);

                    return true;
                }*/

                $this->consume(strlen($captures[1]) + $count - 1);
                $this->tokens[] = $this->generateToken('text', $captures[1]);
                $this->incrementLine(count(explode("\n", $captures[1])) - 1);

                return true;
            } else {
                preg_match('/^\.\.((.|\n)*?)\.\./', $this->input, $captures);
                if ($captures) {

                    $count = 1;
                    if(isset($captures[2]))
                    {
                        $count = 4;
                    }


                    $this->consume(strlen($captures[1]) + $count);
                    $this->tokens[] = $this->generateToken('innerTagText', $captures[1]);
                    $this->incrementLine(count(explode("\n", $captures[1])) - 1);


                    return true;
                } else {
                    preg_match('/^\.(( .*|.*))/', $this->input, $captures);
                    if ($captures) {
                        $this->consume(strlen($captures[2]) + 1);
                        $this->tokens[] = $this->generateToken('innerTagText', $captures[2]);
                        $this->incrementColumn(strlen($captures[2]) + 1);

                        return true;
                    }
                }
            }
        }
    }

    private function isDocType()
    {
        if(preg_match('/^doctype *([^\n]*)/', $this->input, $captures))
        {
            $this->tokens[] = $this->generateToken('doctype', $captures[1]);
            $this->consume(strlen($captures[0]));
            return true;
        }
    }

    private function isStatement()
    {
        //^{{(.*)}}

        //^set ((.*)=(.*))

        //^set (.*)
    }

    private function isComment()
    {
        preg_match('/^<!(.*)/', $this->input, $captures);
        if($captures)
        {
            if(isset($captures[1]))
            {
                $this->consume(strlen($captures[1]) + 2);
            } else {
                $this->consume(2);
            }
        }
    }

    private function parseInput()
    {
        return
            $this->isEoF() ||
            //$this->isStatement() ||
            $this->isBlank() ||
            $this->isDocType() ||
            $this->isComment() ||
            $this->isTag() ||
          //  $this->isHtml() ||
            $this->isText() ||
            $this->isAttribute() ||
            $this->isIndent();
    }

    /**
     * @param $type
     * @param null $value
     * @return array
     */
    private function generateToken($type, $value = null)
    {
        $token = ["type" => $type, "line" => $this->lineCount, "column" => $this->colCount];

        if($value != null)
        {
            $token["value"] = $value;
        }

        return $token;
    }

    /**
     * @return array
     */
    public function getTokens()
    {
        while(!$this->endOfFile)
        {
            $this->parseInput();
        }

        return $this->tokens;
    }
}