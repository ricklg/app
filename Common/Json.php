<?php
/**
 * Created by PhpStorm.
 * User: gangliu
 * Date: 2019/4/16
 * Time: 下午3:59
 */

namespace Common;
class Json
{
    /**
     * @var Json
     */
    private static $instance;

    private function __construct()
    {
    }

    /**
     * @return Json
     */
    public static function getInstance()
    {
        if (!(self::$instance instanceof self)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * @param $code
     * @param $data
     * @param $msg
     */
    public function json($code, $data, $msg)
    {
        $result = [
            'code' => $code,
            'msg' => $msg,
            'data' => $data === null ? new \stdClass() : $data
        ];

        echo json_encode($result, JSON_UNESCAPED_UNICODE);
        exit;
    }
}