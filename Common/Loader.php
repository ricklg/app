<?php
/**
 * Created by PhpStorm.
 * User: gangliu
 * Date: 2019/4/15
 * Time: 下午5:48
 */

namespace Common;

/**
 * Class Loader
 * @package Common
 */
class Loader
{

    /**
     * @param $class
     */
    static function autoload($class)
    {
        require BASEDIR . '/' . str_replace('\\', '/', $class) . '.php';
    }
}