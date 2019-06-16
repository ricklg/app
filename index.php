<?php
/**
 * Created by PhpStorm.
 * User: gangliu
 * Date: 2019/4/15
 * Time: 下午5:46
 */
define('BASEDIR', __DIR__);
define('LOGDIR', BASEDIR . '/Log/');
include BASEDIR . '/Common/Loader.php';
spl_autoload_register("\\Common\\Loader::autoload");

Common\Application::getInstance()->run();