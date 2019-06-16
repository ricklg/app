<?php
/**
 * Created by PhpStorm.
 * User: gangliu
 * Date: 2019/4/15
 * Time: 下午7:24
 */

namespace Controller;

class Index extends \Common\Controller
{
    public function index()
    {
        self::success(['hello world']);
    }
}