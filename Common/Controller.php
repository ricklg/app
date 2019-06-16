<?php
/**
 * Created by PhpStorm.
 * User: gangliu
 * Date: 2019/4/15
 * Time: 下午7:21
 */

namespace Common;

/**
 * Class Controller
 * @package Common
 */
abstract class Controller
{
    /**
     * @var string
     */
    protected static $controller;
    /**
     * @var string
     */
    protected static $action;

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
    public static $signTime = 100;

    /**
     * @var array
     */
    public static $WhiteListController = ['login'];

    public static $allowActions = [];

    /**
     * Controller constructor.
     * @param $controller
     * @param $action
     */
    public function __construct($controller, $action)
    {
        self::$controller = $controller;
        self::$action = $action;

        foreach ($_SERVER as $name => $value) {
            if (strncmp($name, 'HTTP_', 5) === 0) {
                $name = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))));
                $name = strtolower($name);
                self::$headers[$name] = $value;
            }
        }

        if (in_array(strtolower(self::$controller), self::$WhiteListController)) {
            return true;
        }

        return self::checkSign() && self::checkLogin();
    }

    /**
     * 生成每次请求的sign
     * @return mixed|string
     */
    public static function setSign()
    {
        $data = [
            'version' => self::$headers['version'],
            'os' => self::$headers['os'],
            'time' => self::$headers['time'],
        ];

        // 1 按字段排序
        ksort($data);
        // 2拼接字符串数据  &
        $string = http_build_query($data);
        // 3通过aes来加密
        $string = (new \Common\Aes())->encrypt($string, self::$headers['iv']);

        return $string;
    }

    /**
     * 检查sign
     * @param $data
     * @return bool
     */
    public static function checkSign()
    {

        $str = (new \Common\Aes())->decrypt(self::$headers['sign'], self::$headers['iv']);

        if (empty($str)) {
            self::fail('sign error');
        }

        if (!self::checkHeaderParams($str)) {
            self::fail('header参数错误');
        };

        if (isset(self::$headers['is-test']) && self::$headers['is-test']) {
            return true;
        }

        if ((time() - ceil(self::$headers['time'] / 1000)) > self::$signTime) {
            self::fail('请求过期了');
        }

        if (ErrorLog::getInstance()->getSign() == self::$headers['sign']) {
            self::fail('只能请求一次啊');
        };

        return true;
    }

    /**
     * 登录检测
     * @return bool
     */
    public static function checkLogin()
    {
        if (in_array(self::$controller . '/' . self::$action, self::$allowActions)) {
            return true;
        }

        $token = (new \Common\Aes())->decrypt(self::$headers['token'], self::$headers['iv']);

        if (empty($token)) {
            self::fail('token error');
        }

        if (ErrorLog::getInstance()->getToken() !== $token) {
            self::fail('请登录');
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
            if (!$v || self::$headers[$k] != $v) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param string $msg
     * @param int $code
     * @param array $data
     */
    public static function fail($msg = 'fail', $code = -1, $data = [])
    {
        Json::getInstance()->json($code, $data, $msg);
    }

    /**
     * @param array $data
     * @param string $msg
     * @param int $code
     */
    public static function success($data = [], $msg = 'success', $code = 0)
    {
        ErrorLog::getInstance()->sign(self::$headers['sign']);
        Json::getInstance()->json($code, $data, $msg);
    }

    /**
     * @return string
     */
    public static function setToken()
    {
        $token = md5(uniqid(md5(microtime(true)), true));  //生成不重复字符串
        $token = sha1($token);  //加密
        return $token;
    }
}