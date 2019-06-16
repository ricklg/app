<?php
/**
 * Created by PhpStorm.
 * User: gangliu
 * Date: 2019/4/15
 * Time: 下午7:24
 */

namespace Controller;

use Common\ErrorLog;

class Login extends \Common\Controller
{
    public function index()
    {
        $iv = substr(md5(time()), 0, 16);
        self::$headers['iv'] = $iv;
        self::$headers['time'] = time() . mt_rand(100, 999);
        $sign = self::setSign();
        $data['iv'] = self::$headers['iv'];
        $data['time'] = self::$headers['time'];
        $data['sign'] = $sign;
        $token = self::setToken();
        ErrorLog::getInstance()->token($token);
        $data['token'] = (new \Common\Aes())->encrypt($token, self::$headers['iv']);
        self::success($data);
    }
}