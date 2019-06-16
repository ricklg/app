<?php
/**
 * Created by PhpStorm.
 * User: gangliu
 * Date: 2019/4/16
 * Time: 上午11:40
 */

namespace Common;
/**
 * aes 加密 解密类库
 * Class Aes
 */
class Aes
{
    /**
     * @var mixed|null
     */
    private $key = null;

    /**
     * Aes constructor.
     */
    public function __construct()
    {
        $this->key = '1234567890123456';
    }

    /**
     * 加密
     * @param string $input 加密的字符串
     * @param string $iv
     * @return mixed|string
     */
    public function encrypt($input, $iv)
    {
        $this->validateIv($iv);
        $data = openssl_encrypt($input, $this->getMode($this->key), $this->key, OPENSSL_RAW_DATA, $iv);
        return $this->safe_b64encode($data);
    }

    /**
     * @param string $key
     *
     * @return string
     */
    public function getMode($key)
    {
        return 'aes-' . (8 * strlen($key)) . '-cbc';
    }

    /**
     * 解密
     * @param $sStr解密的字符串
     * @param $iv
     * @return string
     */
    public function decrypt($sStr, $iv)
    {
        $this->validateIv($iv);
        $decrypted = openssl_decrypt($this->safe_b64decode($sStr), self::getMode($this->key), $this->key, OPENSSL_RAW_DATA, $iv);
        return $decrypted;
    }

    /**
     * @param string $key
     */
    public function validateKey(string $key)
    {
        if (!in_array(strlen($key), [16, 24, 32], true)) {
            throw new \Exception(sprintf('Key length must be 16, 24, or 32 bytes; got key len (%s).', strlen($key)));
        }
    }

    /**
     * @param string $iv
     */
    public function validateIv(string $iv)
    {
        if (!empty($iv) && 16 !== strlen($iv)) {
            throw new \Exception('IV length must be 16 bytes.');
        }
    }

    /**
     * 处理特殊字符
     * @param $string
     * @return mixed|string
     */
    public function safe_b64encode($string)
    {
        $data = base64_encode($string);
        $data = str_replace(array('+', '/', '='), array('-', '_', ''), $data);
        return $data;
    }

    /**
     * 解析特殊字符
     * @param $string
     * @return bool|string
     */
    public function safe_b64decode($string)
    {
        $data = str_replace(array('-', '_'), array('+', '/'), $string);
        $mod4 = strlen($data) % 4;
        if ($mod4) {
            $data .= substr('====', $mod4);
        }
        return base64_decode($data);
    }

    /**
     * 生成IV
     */
    public function generateIV()
    {
        $ivLength = openssl_cipher_iv_length($this->getMode($this->key));
        $iv = openssl_random_pseudo_bytes($ivLength, $isStrong);
        if (false === $iv && false === $isStrong) {
            throw new \Exception('IV generate failed.');
        }

        return $iv;
    }
}