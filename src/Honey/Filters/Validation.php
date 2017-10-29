<?php
/**
 * User: tuttarealstep
 * Date: 06/05/17
 * Time: 17.18
 */

namespace Honey\Filters;

class Validation
{
    /**
     * @var mixed
     */
    protected $form;

    /**
     * @var int
     */
    protected $filter = FILTER_DEFAULT;

    /**
     * @var array
     */
    protected $options = [];

    /**
     * @var array
     */
    protected $flags = [];

    /**
     * @param string $email
     * @param int $filter
     * @return $this
     */
    public function email($email, $filter = FILTER_VALIDATE_EMAIL)
    {
        $this->setForm($email);
        $this->setFilter($filter);

        return $this;
    }

    /**
     * @param $form
     * @return $this
     */
    public function setForm($form)
    {
        $this->form = $form;

        return $this;
    }

    /**
     * @param int $filter
     * @return $this
     */
    public function setFilter(int $filter)
    {
        $this->filter = $filter;

        return $this;
    }

    /**
     * @param $variable
     * @param int $filter
     * @return $this
     */
    public function boolean($variable, $filter = FILTER_VALIDATE_BOOLEAN)
    {
        $this->setForm($variable);
        $this->setFilter($filter);

        return $this;
    }

    /**
     * @param $variable
     * @param int $filter
     * @return $this
     */
    public function float($variable, $filter = FILTER_VALIDATE_FLOAT)
    {
        $this->setForm($variable);
        $this->setFilter($filter);

        return $this;
    }

    /**
     * @param $variable
     * @param int $filter
     * @return $this
     */
    public function int($variable, $filter = FILTER_VALIDATE_INT)
    {
        $this->setForm($variable);
        $this->setFilter($filter);

        return $this;
    }

    /**
     * @param $variable
     * @param int $filter
     * @return $this
     */
    public function ip($variable, $filter = FILTER_VALIDATE_IP)
    {
        $this->setForm($variable);
        $this->setFilter($filter);

        return $this;
    }

    /**
     * @param $variable
     * @param int $filter
     * @return $this
     */
    public function mac($variable, $filter = FILTER_VALIDATE_MAC)
    {
        $this->setForm($variable);
        $this->setFilter($filter);

        return $this;
    }

    /**
     * @param $variable
     * @param int $filter
     * @return $this
     */
    public function regexp($variable, $filter = FILTER_VALIDATE_REGEXP)
    {
        $this->setForm($variable);
        $this->setFilter($filter);

        return $this;
    }

    /**
     * @param $variable
     * @param int $filter
     * @return $this
     */
    public function url($variable, $filter = FILTER_VALIDATE_URL)
    {
        $this->setForm($variable);
        $this->setFilter($filter);

        return $this;
    }

    /**
     * @param bool $toBoolean
     * @return bool|mixed
     */
    public function isValid($toBoolean = false)
    {
        $filteredVar = filter_var($this->form, $this->filter, ['options' => $this->options, 'flags' => $this->parseFlags($this->flags)]);

        return ($toBoolean == true) ? $this->toBoolean($filteredVar) : $filteredVar;
    }

    /**
     * @param $flags
     * @return mixed
     */
    protected function parseFlags($flags)
    {
        if (count($flags) == 1) {
            return $flags[0];
        }

        return $flags;
    }

    public function toBoolean($variable)
    {
        if ($variable && $variable != null) {
            return true;
        }

        return false;
    }

    /**
     * @param array $options
     * @return $this
     */
    public function setOptions(array $options)
    {
        $this->options = $options;

        return $this;
    }

    /**
     * @param $option
     * @param $value
     * @return $this
     */
    public function setOption($option, $value)
    {
        $this->options[ $option ] = $value;

        return $this;
    }

    /**
     * @param array $flags
     * @return $this
     */
    public function setFlags(array $flags)
    {
        $this->flags = $flags;

        return $this;
    }

    /**
     * @param $flag
     * @return $this
     */
    public function setFlag($flag)
    {
        $this->flags[] = $flag;

        return $this;
    }

    /**
     * @param $email
     * @param bool $toBoolean
     * @return bool|mixed
     */
    public static function validateEmail($email, $toBoolean = false)
    {
        $filteredVar = filter_var($email, FILTER_VALIDATE_EMAIL);

        return ($toBoolean == true) ? self::toBoolean($filteredVar) : $filteredVar;
    }

    /**
     * @param $variable
     * @param bool $toBoolean
     * @return bool|mixed
     */
    public static function validateBoolean($variable, $toBoolean = false)
    {
        $filteredVar = filter_var($variable, FILTER_VALIDATE_BOOLEAN);
        return ($toBoolean == true) ? self::toBoolean($filteredVar) : $filteredVar;
    }

    /**
     * @param $variable
     * @param bool $toBoolean
     * @return bool|mixed
     */
    public static function validateFloat($variable, $toBoolean = false)
    {
        $filteredVar = filter_var($variable, FILTER_VALIDATE_FLOAT);
        return ($toBoolean == true) ? self::toBoolean($filteredVar) : $filteredVar;
    }

    /**
     * @param $variable
     * @param bool $toBoolean
     * @return bool|mixed
     */
    public static function validateInt($variable, $toBoolean = false)
    {
        $filteredVar = filter_var($variable, FILTER_VALIDATE_INT);
        return ($toBoolean == true) ? self::toBoolean($filteredVar) : $filteredVar;
    }

    /**
     * @param $variable
     * @param bool $toBoolean
     * @return bool|mixed
     */
    public static function validateIp($variable, $toBoolean = false)
    {
        $filteredVar = filter_var($variable, FILTER_VALIDATE_IP);
        return ($toBoolean == true) ? self::toBoolean($filteredVar) : $filteredVar;
    }

    /**
     * @param $variable
     * @param bool $toBoolean
     * @return bool|mixed
     */
    public static function validateMac($variable, $toBoolean = false)
    {
        $filteredVar = filter_var($variable, FILTER_VALIDATE_MAC);
        return ($toBoolean == true) ? self::toBoolean($filteredVar) : $filteredVar;
    }

    /**
     * @param $variable
     * @param bool $toBoolean
     * @return bool|mixed
     */
    public static function validateRegexp($variable, $toBoolean = false)
    {
        $filteredVar = filter_var($variable, FILTER_VALIDATE_REGEXP);
        return ($toBoolean == true) ? self::toBoolean($filteredVar) : $filteredVar;
    }

    /**
     * @param $variable
     * @param bool $toBoolean
     * @return bool|mixed
     */
    public static function validateUrl($variable, $toBoolean = false)
    {
        $filteredVar = filter_var($variable, FILTER_VALIDATE_URL);
        return ($toBoolean == true) ? self::toBoolean($filteredVar) : $filteredVar;
    }

}