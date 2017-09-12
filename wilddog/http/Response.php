<?php
/**
 * Created by PhpStorm.
 * User: jnduan
 * Date: 2016/12/26
 * Time: 下午4:27
 */

namespace wilddog\http;


class Response
{
    protected $headers;
    protected $content;
    protected $statusCode;

    public function __construct($statusCode, $content, $headers = array())
    {
        $this->statusCode = $statusCode;
        $this->content = $content;
        $this->headers = $headers;
    }

    /**
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @return mixed
     */
    public function getJSONContent()
    {
        return json_decode($this->content, true);
    }

    /**
     * @return mixed
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @return bool
     */
    public function ok()
    {
        return $this->getStatusCode() < 400;
    }

    public function __toString()
    {
        return '[Response] HTTP ' . $this->getStatusCode() . ' ' . $this->getContent();
    }
}