<?php
/**
 * Created by PhpStorm.
 * User: gangliu
 * Date: 2019/4/16
 * Time: 上午11:08
 */

namespace Common;
class ErrorLog
{
    /**
     * @var string
     */
    public static $logUrl;

    /**
     * @var string
     */
    public static $signUrl;

    /**
     * @var string
     */
    public static $tokenUrl;

    /**
     * @var ErrorLog
     */
    private static $instance;

    private function __construct()
    {
        self::$logUrl = LOGDIR . '/log.log';
        self::$signUrl = LOGDIR . '/sign.sign';
        self::$tokenUrl = LOGDIR . '/token.token';
    }

    /**
     * @return ErrorLog
     */
    public static function getInstance()
    {
        if (!(self::$instance instanceof self)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * @param $data
     */
    public function token($data)
    {
        $this->write(self::$tokenUrl, $data);
    }

    /**
     * @return bool|string
     */
    public function getToken()
    {
        if (!is_file(self::$tokenUrl)) {
            throw new \Exception('token file not found');
        }
        return file_get_contents(self::$tokenUrl);
    }

    /**
     * @param $data
     */
    public function sign($data)
    {
        $this->write(self::$signUrl, $data);
    }

    /**
     * @return bool|string
     */
    public function getSign()
    {
        if (!is_file(self::$signUrl)) {
            throw new \Exception('sign file not found');
        }
        return file_get_contents(self::$signUrl);
    }

    /**
     * @param $data
     */
    public function log($data)
    {
        $this->write(self::$logUrl, date('Y-m-d H:i:s ') . $data . PHP_EOL, FILE_APPEND);
    }

    /**
     * @param $url
     * @param $data
     */
    private function write($url, $data, $flags = 0)
    {
        $dir_name = dirname($url);

        //目录不存在就创建
        if (!is_dir($dir_name)) {
            mkdir($dir_name, 0777, true);
            chmod($dir_name, 0777);
        }

        file_put_contents($url, $data, $flags);

        $mode = substr(sprintf("%o", fileperms($url)), -4);
        if ($mode != '0777') {
            chmod($url, 0777);
        }
    }

}