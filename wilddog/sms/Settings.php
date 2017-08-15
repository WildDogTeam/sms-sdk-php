<?php
/**
 * Created by PhpStorm.
 * User: jnduan
 * Date: 2016/12/27
 * Time: 上午10:41
 */

namespace wilddog\sms;


class Settings
{
    protected $appId;
    protected $secret;

    /**
     * Settings constructor.
     * @param $appId
     * @param $secret
     */
    public function __construct($appId, $secret)
    {
        $this->appId = $appId;
        $this->secret = $secret;
    }

    /**
     * @return mixed
     */
    public function getAppId()
    {
        return $this->appId;
    }

    /**
     * @return mixed
     */
    public function getSecret()
    {
        return $this->secret;
    }

}