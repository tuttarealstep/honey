<?php
/**
 * User: tuttarealstep
 * Date: 02/05/17
 * Time: 18.28
 */

namespace Honey\Cryptography;

class Cryptography
{
    /**
     * @var string
     */
    protected $key;

    /**
     * @var string
     */
    protected $cipher;

    /**
     * @var int
     */
    protected $cost = 10;

    function __construct($key, $cost = 10, $cipher = 'aes-128-ecb')
    {
        $this->key = $key;
        $this->cipher = $cipher;
        $this->cost = $cost;
    }

    /**
     * @param $data
     * @return string|bool
     */
    public function encrypt($data)
    {
        return openssl_encrypt($data, $this->cipher, $this->key);
    }

    /**
     * @param $data
     * @return string|bool
     */
    public function decrypt($data)
    {
        $data = openssl_decrypt($data, $this->cipher, $this->key);
        return $data;
    }

    /**
     * @param $data
     * @param string $algorithm
     * @return string
     */
    public function hash($data, $algorithm = 'sha256')
    {
        return hash($algorithm, $data . $this->key);
    }

    /**
     * @param $password
     * @param int $algorithm
     * @return bool|string
     */
    public function passwordHash($password, $algorithm = PASSWORD_BCRYPT)
    {
        return password_hash($password . $this->key, $algorithm, ['cost' => $this->cost]);
    }

    /**
     * @param $hash
     * @param int $algorithm
     * @return bool
     */
    public function passwordNeedRehash($hash, $algorithm = PASSWORD_BCRYPT)
    {
        return password_needs_rehash($hash, $algorithm, ['cost' => $this->cost]);
    }

    /**
     * @param $password
     * @param $hash
     * @return bool
     */
    public function passwordVerify($password, $hash)
    {
        return password_verify($password . $this->key, $hash);
    }

    /**
     * @param int $cost
     */
    public function setCost(int $cost)
    {
        $this->cost = $cost;
    }

    /**
     * @param $length
     * @return bool|string
     */
    public static function generateRandom($length)
    {
        switch(true)
        {
            case function_exists("random_bytes") :
                $random = random_bytes($length);
                break;
            case function_exists("openssl_random_pseudo_bytes") :
                $random = openssl_random_pseudo_bytes($length);
                break;
            default :
                $i = 0;
                $random = "";
                while($i < $length):
                    $i++;
                    $random .= chr(mt_rand(0, 255));
                endwhile;
                break;
        }
        return substr(bin2hex($random), 0, $length);
    }
}
