<?php
/**
 * User: tuttarealstep
 * Date: 06/05/17
 * Time: 12.59
 */

namespace Honey\Cache;

class SimpleCache
{
    /**
     * @var string
     */
    protected $path;

    /**
     * @var string
     */
    private $extension = '.cache';

    /**
     * SimpleCache constructor.
     * @param string $path
     */
    function __construct(string $path)
    {
        if(file_exists($path))
        {
            $this->path = $path;
        } else {
            throw new \LogicException('Path not found');
        }
    }

    /**
     * @param $key
     * @param $value
     * @param int $expireTime
     */
    public function set($key, $value, $expireTime = 0)
    {
        $value = serialize($value);
        if ($expireTime > 0) {
            file_put_contents($this->path . DIRECTORY_SEPARATOR . md5($key) . $this->extension, '<?php $expireTime = ' . (time() + ($expireTime) / 1000) . '; $value = \'' . $value . '\';');
        } else {
            file_put_contents($this->path . DIRECTORY_SEPARATOR . md5($key). $this->extension, '<?php $value = \'' . $value . '\';');
        }
    }

    /**
     * Get the current cached value identified by the key
     *
     * @param $key
     * @return bool|mixed
     */
    public function get($key)
    {
        @include $this->path . DIRECTORY_SEPARATOR . md5($key). $this->extension;

        if (isset($value) && isset($expireTime) && ($expireTime < time())) {
            $bckValue = isset($value) ? @unserialize($value) : false;
            @unlink($this->path . DIRECTORY_SEPARATOR . md5($key). $this->extension);

            return $bckValue;
        }

        return isset($value) ? @unserialize($value) : false;
    }

    /**
     * Delete a saved value
     * @param $key
     * @return bool
     */
    public function clear($key)
    {

        if (file_exists($this->path . DIRECTORY_SEPARATOR . md5($key). $this->extension)) {
            @unlink($this->path . DIRECTORY_SEPARATOR . md5($key). $this->extension);

            return true;
        }

        return false;
    }
}