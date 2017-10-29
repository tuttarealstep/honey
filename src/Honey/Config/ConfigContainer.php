<?php
/**
 * User: tuttarealstep
 * Date: 28/04/17
 * Time: 19.04
 */

namespace Honey\Config;

use ArrayAccess;

class ConfigContainer implements ArrayAccess
{
    /**
     * @var array
     */
    protected $container = [];

    function __construct($container = [])
    {
        $this->container = $container;
    }

    /**
     * Whether a offset exists
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     * @since 5.0.0
     */
    public function offsetExists($offset) : bool
    {
        return isset($this->container[$offset]);
    }

    /**
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return mixed Can return all value types.
     * @since 5.0.0
     */
    public function offsetGet($offset)
    {
        return isset($this->container[$offset]) ? $this->container[$offset] : null;
    }

    /**
     * Offset to set
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetSet($offset, $value = null)
    {
        if(is_array($offset) && $value == null)
        {
            $offset = isset($offset['key']) ? $offset['key'] : $offset[0];
            $value = isset($offset['value']) ? $offset['value'] : $offset[1];

            $this->container[$offset] = $value;
        } elseif ($value == null)
        {
            $this->container[] = $value;
        } else {
            $this->container[$offset] = $value;
        }
    }

    /**
     * Offset to unset
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetUnset($offset)
    {
        unset($this->container[$offset]);
    }

    /**
     * @param $offset
     * @param $value
     */
    public function set($offset, $value) : void
    {
        $this->offsetSet($offset, $value);
    }

    /**
     * @param $offset
     * @return mixed
     */
    public function get($offset)
    {
        return $this->offsetGet($offset);
    }

    /**
     * @param $offset
     * @return bool
     */
    public function has($offset) : bool
    {
        return $this->offsetExists($offset);
    }

    /**
     * @param $offset
     */
    public function delete($offset)
    {
        $this->offsetUnset($offset);
    }
}