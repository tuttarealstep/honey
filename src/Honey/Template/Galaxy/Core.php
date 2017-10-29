<?php
/**
 * User: tuttarealstep
 * Date: 06/05/17
 * Time: 19.34
 */

namespace Honey\Template\Galaxy;

class Core
{
    /**
     * @var string
     */
    protected $cachePath;

    /**
     * @var string
     */
    protected $cacheExtension = '.cache';

    /**
     * @var array
     */
    protected $options = [];

    /**
     * @var array
     */
    protected $variables = [];

    function __construct($options)
    {
        $this->options = $options;
    }

    /**
     * @param $key
     * @return bool|mixed
     */
    protected function loadCache($key)
    {
        if($this->options['cachePath'] == null)
            return false;

        if(file_exists($this->options['cachePath'] . DIRECTORY_SEPARATOR . md5($key) . $this->cacheExtension))
        {
            include $this->options['cachePath'] . DIRECTORY_SEPARATOR . md5($key) . $this->cacheExtension;

            return isset($value) ? @unserialize($value) : false;
        }

        return false;
    }

    /**
     * @param string $key
     * @param $content
     * @return bool
     */
    protected function saveCache($key, $content)
    {
        if($this->options['cachePath'] == null)
            return false;

        $content = serialize($content);
        file_put_contents($this->options['cachePath'] . DIRECTORY_SEPARATOR . md5($key). $this->cacheExtension, '<?php $value = \'' . $content . '\';');
        return true;
    }

    /**
     * @param string|null $content
     * @return bool|mixed
     */
    protected function loadTemplateCache(string $content = null)
    {
        if(isset($this->options["filePath"]) && isset($this->options["cache"]) && $this->options["cache"] == true && $this->loadCache($this->options["filePath"]))
        {
            return $this->loadCache($this->options["filePath"]);
        } else {
            if($content == null)
            {
                if(file_exists($this->options["filePath"]))
                {
                    $content = file_get_contents($this->options["filePath"]);
                } else {
                    $content = '';
                }
            }

            $content = $this->compileBody($content);

            if(isset($this->options["cache"]) && $this->options["cache"] == true)
            {
                $this->saveCache($this->options["filePath"], $content);
            }

            return $content;
        }
    }

    protected function compileBody(string $content)
    {
        $this->options["variables"] = $this->variables;
        $compiler = new Compiler($content, $this->options);
        $contentCompiled = $compiler->compile();

        return $contentCompiled;
    }

    private function parseVariables($content)
    {
        foreach ($this->variables as $tag => $value) {
            if (is_object($value)) {
               /* print_r($value);
                die();*/
               //todo obj
            } elseif (is_array($value))
            {
                /*preg_match('/^{{(.*)}}/', $tag, $test);
                print_r($test);
                die();*/
                //todo array
            } else {
                $content = str_ireplace('{@' . $tag . '@}', $value, $content);
                $content = str_ireplace('{@no_' . $tag . '@}', '{@' . $tag . '@}', $content);
            }
        }

        return $content;
    }


    /**
     * @param string $content
     * @return string
     */
    public function compile(string $content)
    {
        $content = $this->compileBody($content);
        return $this->lastProcessor($content);
    }

    /**
     * @param $filePath
     * @return bool|mixed
     */
    public function compileFile($filePath)
    {
        $this->options["filePath"] = $filePath;
        return $this->lastProcessor($this->loadTemplateCache());
    }

    public function lastProcessor($content)
    {
        return $this->parseVariables($content);
    }

    /**
     * @param array $variables
     */
    public function setVariables(array $variables)
    {
        $this->variables = $variables;
    }

    public function addVariable($key, $value)
    {
        $this->variables[$key] = $value;
    }

    public function removeVariable($key)
    {
        unset($this->variables[$key]);
    }

    public function getVariables()
    {
        return $this->variables;
    }
}