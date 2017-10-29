<?php
/**
 * User: tuttarealstep
 * Date: 23/05/17
 * Time: 17.28
 */
namespace Honey\Template\Simple;

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

            $newKey = preg_replace('/[0-9]+/', '', md5($key));
            die($newKey);
            //return new $newKey->printContent();
            //return isset($value) ? $value : false;
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

        file_put_contents($this->options['cachePath'] . DIRECTORY_SEPARATOR . md5($key). $this->cacheExtension, '<?php $value = \'' . $content . '\';');
        return true;
    }

    /**
     * @return bool|mixed
     */
    protected function loadTemplateCache()
    {
        if(isset($this->options["filePath"]) && isset($this->options["cache"]) && $this->options["cache"] == true && $this->loadCache($this->options["filePath"]))
        {
            return $this->loadCache($this->options["filePath"]);
        } else {

            if(file_exists($this->options["filePath"]))
            {

                $this->options["variables"] = $this->variables;

                ob_start();
                    include($this->options["filePath"]);
                $content = ob_get_clean();

                if(isset($this->options["cache"]) && $this->options["cache"] == true)
                {
                    $this->saveCache($this->options["filePath"], file_get_contents($this->options["filePath"]));
                }

                return $content;
            } else {
                return false;
            }
        }
    }

    private function parseVariables($content)
    {
        foreach ($this->variables as $tag => $value) {
            if(is_array($value))
            {
                continue;
            }
            $content = str_ireplace('{@' . $tag . '@}', $value, $content);
            $content = str_ireplace('{@no_' . $tag . '@}', '{@' . $tag . '@}', $content);
        }

        return $content;
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

    public function __set($k, $v)
    {
        $this->variables[$k] = $v;
    }

    public function __get($k)
    {
        return $this->variables[$k];
    }
}
