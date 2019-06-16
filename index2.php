<?php
/**
 * Created by PhpStorm.
 * User: gangliu
 * Date: 2019/4/15
 * Time: 上午10:53
 */

//error_reporting(E_ERROR);

class Common
{
    /**
     * @var Common
     */
    private static $instance;

    /**
     * @var array
     */
    public static $headers;

    /**
     * @var array
     */
    public static $headerParams = ['version', 'os', 'time'];

    /**
     * @var int
     */
    public static $signTime = 1000;

    /**
     * @var string
     */
    public static $url;

    /**
     * Common constructor.
     */
    private function __construct()
    {
        foreach ($_SERVER as $name => $value) {
            if (strncmp($name, 'HTTP_', 5) === 0) {
                $name = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))));
                $name = strtolower($name);
                self::$headers[$name] = $value;
            }
        }

        self::$url = './' . date('Y-m-d') . '/sign.sign';
    }

    /**
     * @return Common
     */
    public static function getInstance()
    {
        if (!(self::$instance instanceof self)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * 生成每次请求的sign
     * @param array $data
     * @return string
     */
    public static function setSign()
    {
        $data = [
            'version' => self::$headers['version'],
            'os' => self::$headers['os'],
            'time' => self::$headers['time']
        ];
        // 1 按字段排序
        ksort($data);
        // 2拼接字符串数据  &
        $string = http_build_query($data);
        // 3通过aes来加密
        $string = (new Aes())->encrypt($string);

        self::writeSign($string);

        return $string;
    }

    /**
     * 检查sign
     * @param $data
     * @return bool
     */
    public static function checkSign()
    {

        $str = (new Aes())->decrypt(self::$headers['sign']);

        if (empty($str)) {
            exit('sign error');
        }

        if (!self::checkHeaderParams($str)) {
            exit('参数错误');
        };

        if ((time() - ceil(self::$headers['time'] / 1000)) > self::$signTime) {
            exit('请求过期了');
        }

        if (file_get_contents(self::$url) === self::$headers['sign']) {
            exit('只能请求一次啊');
        };

        return true;
    }

    /**
     * 判断header参数
     * @param $str
     * @return bool
     */
    public static function checkHeaderParams($str)
    {
        parse_str($str, $arr);
        $headerParams = self::$headerParams;
        asort($headerParams);

        if (!is_array($arr) || array_keys($arr) != array_values($headerParams)) {
            return false;
        }

        foreach ($arr as $k => $v) {
            if (self::$headers[$k] != $v) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param $data
     */
    public static function writeSign($data)
    {
        //设置路径目录信息
        $url = self::$url;
        $dir_name = dirname($url);

        //目录不存在就创建
        if (!is_dir($dir_name)) {
            mkdir($dir_name, 0777, true);
            chmod($dir_name, 0777);
        }
        chmod($url, 0777);
        file_put_contents($url, $data);
    }

}

//exit(Common::getInstance()->setSign());

var_dump(Common::getInstance()->checkSign());

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
     * @var null|string
     */
    private $iv = null;

    /**
     * Aes constructor.
     */
    public function __construct()
    {
        $this->key = '1234567890123456';
        $this->iv = '1234561234567890';
        //self::validateKey($this->key);
        //self::validateIv($this->key);
    }

    /**
     * 加密
     * @param string $input 加密的字符串
     * @return mixed|string
     */
    public function encrypt($input = '')
    {
        $data = openssl_encrypt($input, self::getMode($this->key), $this->key, OPENSSL_RAW_DATA, $this->iv);
        return $this->safe_b64encode($data);
    }

    /**
     * @param string $key
     *
     * @return string
     */
    public static function getMode($key)
    {
        return 'aes-' . (8 * strlen($key)) . '-cbc';
    }

    /**
     * 解密
     * @param String $sStr 解密的字符串
     * @return string
     */
    public function decrypt($sStr)
    {
        $decrypted = openssl_decrypt($this->safe_b64decode($sStr), self::getMode($this->key), $this->key, OPENSSL_RAW_DATA, $this->iv);
        return $decrypted;
    }

    /**
     * @param string $key
     * @throws Exception
     */
    public static function validateKey(string $key)
    {
        if (!in_array(strlen($key), [16, 24, 32], true)) {
            throw new \Exception(sprintf('Key length must be 16, 24, or 32 bytes; got key len (%s).', strlen($key)));
        }
    }

    /**
     * @param string $iv
     * @throws Exception
     */
    public static function validateIv(string $iv)
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
    function safe_b64encode($string)
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
    function safe_b64decode($string)
    {
        $data = str_replace(array('-', '_'), array('+', '/'), $string);
        $mod4 = strlen($data) % 4;
        if ($mod4) {
            $data .= substr('====', $mod4);
        }
        return base64_decode($data);
    }

}