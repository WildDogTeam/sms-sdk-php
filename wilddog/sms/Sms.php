<?php
/**
 * Created by PhpStorm.
 * User: jnduan
 * Date: 2016/12/27
 * Time: 上午10:35
 */

namespace wilddog\sms;

use wilddog\http\CurlClient;
use wilddog\exceptions\WilddogException;

/**
 * Class Sms
 * @package wilddog\sms
 */
class Sms
{
    protected $settings;

    /**
     * Sms constructor.
     * @param $settings
     */
    public function __construct(Settings $settings)
    {
        $this->settings = $settings;
    }


    /**
     * @param $mobile
     * @return bool
     */
    private function isPhoneNumberValid($mobile)
    {
        if (preg_match('/^1\d{10}$/', $mobile)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param $templateId
     * @return bool
     */
    private function isTemplateIdValid($templateId)
    {
        if (preg_match('/^\d{6,}$/', $templateId)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param $code
     * @return bool
     */
    private function isSmsCodeValid($code)
    {
        if (preg_match('/^\d{6}$/', $code)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param $mobile
     * @param $templateId
     * @param Settings $settings
     * @return \wilddog\http\Response
     * @throws WilddogException
     */
    public function sendCode($mobile, $templateId, Settings $settings, $var = array())
    {

        if (!self::isPhoneNumberValid($mobile)) {
            throw new WilddogException('Invalid phone number.');
        }

        if (!self::isTemplateIdValid($templateId)) {
            throw new WilddogException('Invalid template id.');
        }

        $params = array(
            'mobile' => $mobile,
            'templateId' => $templateId,
            'timestamp' => round(microtime(true) * 1000)
        );

        if(sizeof($var) > 0) {
            $params["params"] = json_encode($var);
            ksort($params);
        }

        $signature = hash('sha256', urldecode(http_build_query($params) . '&' . $settings->getSecret()), false);

        $params['signature'] = $signature;

        $client = new CurlClient();

        $response = $client->request(
            'POST',
            'https://sms.wilddog.com/api/v1/' . $settings->getAppId() . '/code/send',
            $params
        );

        return $response;
    }

    /**
     * @param $mobile
     * @param $code
     * @param $settings
     * @return \wilddog\http\Response
     * @throws WilddogException
     */
    public function checkCode($mobile, $code)
    {
        if (!self::isPhoneNumberValid($mobile)) {
            throw new WilddogException('Invalid phone number.');
        }

        if (!self::isSmsCodeValid($code)) {
            throw new WilddogException('Invalid template id.');
        }

        $params = array(
            'code' => $code,
            'mobile' => $mobile,
            'timestamp' => round(microtime(true) * 1000)
        );

        $signature = hash('sha256', urldecode(http_build_query($params) . '&' . $this->settings->getSecret()), false);

        $params['signature'] = $signature;

        $client = new CurlClient();

        $response = $client->request(
            'POST',
            'https://sms.wilddog.com/api/v1/' . $this->settings->getAppId() . '/code/check',
            $params
        );

        return $response;
    }

    /**
     * @param array $mobiles
     * @param $templateId
     * @param array $params
     * @param $settings
     * @return \wilddog\http\Response
     * @throws WilddogException
     */
    public function sendNotify($mobiles = array(), $templateId, $params = array())
    {
        if(count($mobiles) == 0) {
            throw new WilddogException('Empty mobiles array');
        }

        foreach ($mobiles as $mobile) {
            if (!self::isPhoneNumberValid($mobile)) {
                throw new WilddogException('Invalid phone number.');
            }
        }

        if (!self::isSmsCodeValid($templateId)) {
            throw new WilddogException('Invalid template id.');
        }

        $params = array(
            'mobiles' => json_encode($mobiles),
            'params' => json_encode($params),
            'templateId' => $templateId,
            'timestamp' => round(microtime(true) * 1000)
        );

        $signature = hash('sha256', urldecode(http_build_query($params) . '&' . $this->settings->getSecret()), false);

        $params['signature'] = $signature;

        $client = new CurlClient();

        $response = $client->request(
            'POST',
            'https://sms.wilddog.com/api/v1/' . $this->settings->getAppId() . '/notify/send',
            $params
        );

        return $response;
    }

    /**
     * @param array $mobiles
     * @param $content
     * @param string $extno
     * @param $rrid
     * @return \wilddog\http\Response
     * @throws WilddogException
     */
    public function sendMarketing($mobiles = array(), $content, $extno = "0", $rrid) {
        if(count($mobiles) == 0) {
            throw new WilddogException('Empty mobiles array');
        }

        foreach ($mobiles as $mobile) {
            if (!self::isPhoneNumberValid($mobile)) {
                throw new WilddogException('Invalid phone number.');
            }
        }

        $params = array(
            'content' => $content,
            'extno' => $extno,
            'mobiles' => json_encode($mobiles),
            'timestamp' => round(microtime(true) * 1000)
        );

        $post_data = urldecode(http_build_query($params) . '&' . $this->settings->getSecret());

        $signature = hash('sha256', $post_data, false);

        $params['signature'] = $signature;

        if(!is_null($rrid)  && is_string($rrid)) {
            $params['rrid'] = $rrid;
        }

        $client = new CurlClient();

        $response = $client->request(
            'POST',
            'https://sms.wilddog.com/api/v1/' . $this->settings->getAppId() . '/marketing/send',
            $params
        );

        return $response;
    }

    /**
     * @param $sendId
     * @return \wilddog\http\Response
     */
    public function checkStatus($sendId)
    {
        $params = array(
            'rrid' => $sendId
        );

        $signature = hash('sha256', urldecode(http_build_query($params) . '&' . $this->settings->getSecret()), false);

        $params['signature'] = $signature;

        $client = new CurlClient();

        $response = $client->request(
            'GET',
            'https://sms.wilddog.com/api/v1/' . $this->settings->getAppId() . '/status',
            $params
        );

        return $response;
    }

    /**
     * @return \wilddog\http\Response
     */
    public function getBalance()
    {
        $params = array(
            'timestamp' => round(microtime(true) * 1000)
        );

        $signature = hash('sha256', urldecode(http_build_query($params) . '&' . $this->settings->getSecret()), false);

        $params['signature'] = $signature;

        $client = new CurlClient();

        $response = $client->request(
            'GET',
            'https://sms.wilddog.com/api/v1/' . $this->settings->getAppId() . '/getBalance',
            $params
        );

        return $response;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return 'Wilddog.Sms';
    }

}