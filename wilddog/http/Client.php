<?php
/**
 * Created by PhpStorm.
 * User: jnduan
 * Date: 2016/12/26
 * Time: 下午4:25
 */

namespace Wilddog\http;


interface Client
{
    public function request($method, $url, $params = array(), $data = array(),
                            $headers = array(), $user = null, $password = null,
                            $timeout = null);
}