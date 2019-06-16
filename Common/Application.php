<?php
/**
 * Created by PhpStorm.
 * User: gangliu
 * Date: 2019/4/15
 * Time: 下午6:02
 */

namespace Common;

/**
 * Class Application
 * @package Common
 */
class Application
{
    /**
     * @var Application
     */
    private static $instance;

    private function __construct()
    {
    }

    /**
     * @return Application
     */
    public static function getInstance()
    {
        if (!(self::$instance instanceof self)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * 执行
     * @return mixed
     */
    public function run()
    {
        $this->register();
        $uri = $_SERVER['REQUEST_URI'];
        list($c, $v) = explode('/', trim($uri, '/'));

        $c_low = strtolower($c);
        $c = ucwords($c_low);
        $class = '\\Controller\\' . $c;
        $obj = new $class($c, $v);
        return $obj->$v();
    }

    /**
     * 注册处理函数
     */
    public function register()
    {
        error_reporting(E_ERROR);
        set_error_handler([$this, 'error_handler']);
        set_exception_handler([$this, 'exception_handler']);
        register_shutdown_function([$this, 'shutdown_function']);
    }

    /**
     * 自定义错误处理函数
     *
     * @param $errNo
     * @param $errMessage
     * @param $errFile
     * @param $errLine
     */
    public function error_handler($errno, $errstr, $errfile, $errline)
    {
        ErrorLog::getInstance()->log($errno . '|' . $errstr . '|' . $errfile . '|' . $errline);
        switch ($errno) {
            case E_ERROR:
            case E_CORE_ERROR:
            case E_COMPILE_ERROR:
                $msg = "ERROR: [ID $errno] $errstr (Line: $errline of $errfile)";

                Json::getInstance()->json(-1, [], $msg);
                break;
            case E_WARNING:
            case E_CORE_WARNING:
            case E_COMPILE_WARNING:
                $msg = "WARNING: [ID $errno] $errstr (Line: $errline of $errfile)";

                Json::getInstance()->json(-1, [], $msg);
                break;
            default:
                //不显示Notice级的错误
                break;
        }
    }

    /**
     * 自定义异常处理函数
     * @param \Exception $exception
     */
    public function exception_handler($exception)
    {
        ErrorLog::getInstance()->log('Exception: [CODE ' . $exception->getCode() . '] ' . $exception->getMessage() . ' (Line: ' . $exception->getLine() . ' of ' . $exception->getFile() . ')');
        Json::getInstance()->json(-1, [], $exception->getMessage());
    }

    /**
     * 后续处理函数
     */
    public function shutdown_function()
    {
        $last_error = error_get_last();

        if (isset($last_error) &&
            ($last_error['type'] & (E_ERROR | E_WARNING | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING))
        ) {
            $this->error_handler($last_error['type'], $last_error['message'], $last_error['file'], $last_error['line']);
        }
    }
}
