<?php
/**
 * User: tuttarealstep
 * Date: 02/05/17
 * Time: 11.29
 */

namespace Honey\Http;

class Response
{
    /**
     * @var mixed
     */
    protected $content;

    /**
     * @var array
     */
    protected $headers = [];

    /**
     * @var int
     */
    protected $status;

    public function __construct(string $content = '', int $status = 200, array $headers = [])
    {
        $this->content = $content;
        $this->status = $status;
        $this->headers = $headers;
    }

    public function jsonHeader()
    {
        $this->header('Content-Type', 'application/json', true);
    }

    public function header(string $fieldName, string $fieldValue, $replace = false)
    {
        if(isset($this->headers[$fieldName]) && $replace)
        {
            $this->headers[$fieldName] = $fieldValue;
        } elseif (!isset($this->headers[$fieldName]))
        {
            $this->headers[$fieldName] = $fieldValue;
        }
    }

    public function setContent($content, $json = false)
    {
        $this->content = $content;

        if($json)
        {
            $this->jsonHeader();
        }
    }

    public function setJsonContent($content)
    {
        $this->setContent(json_encode($content), true);
    }

    public function addContent($content, $json = false)
    {
        $this->content .= $content;

        if($json)
        {
            $this->jsonHeader();
        }
    }

    public function getContent()
    {
        return $this->content;
    }

    protected function compileHeaders()
    {
        foreach ($this->headers as $header => $value)
        {
            header("{$header}: {$value}");
        }
    }

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * @param int $status
     */
    public function setStatus(int $status)
    {
        $this->status = $status;
    }

    /**
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    protected function compileStatus()
    {
        http_response_code($this->status);
    }

    /**
     * @param bool $print
     * @return mixed
     */
    public function sendResponse($print = false)
    {
        $this->compileStatus();
        $this->compileHeaders();

        if($print)
        {
            echo $this->content;
            return null;
        } else {
            return $this->content;
        }
    }

    public function getHeadersList()
    {
        $this->compileHeaders();
        return headers_list();
    }

    public function removeHeader($fieldName)
    {
        if(isset($this->headers[$fieldName]))
        {
            unset($this->headers[$fieldName]);
        }
    }
}