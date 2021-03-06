<?php
/**
 * Created by PhpStorm.
 * User: jnduan
 * Date: 2016/12/26
 * Time: 下午4:28
 */

namespace wilddog\http;

use wilddog\exceptions\WilddogException;

class CurlClient implements Client
{
    const DEFAULT_TIMEOUT = 60;
    protected $curlOptions = array();

    public function __construct(array $options = array())
    {
        $this->curlOptions = $options;
    }

    public function request($method, $url, $params = array(), $data = array(),
                            $headers = array(), $user = null, $password = null,
                            $timeout = null)
    {
        $options = $this->options($method, $url, $params, $data, $headers,
            $user, $password, $timeout);

        try {
            if (!$curl = curl_init()) {
                throw new WilddogException('Unable to initialize cURL');
            }
            if (!curl_setopt_array($curl, $options)) {
                throw new WilddogException(curl_error($curl));
            }
            if (!$response = curl_exec($curl)) {
                throw new WilddogException(curl_error($curl));
            }
            $parts = explode("\r\n\r\n", $response, 3);

            list($head, $body) = ($parts[0] == 'HTTP/1.1 100 Continue')
                ? array($parts[1], $parts[2])
                : array($parts[0], $parts[1]);
            $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $responseHeaders = array();
            $headerLines = explode("\r\n", $head);
            array_shift($headerLines);
            foreach ($headerLines as $line) {
                list($key, $value) = explode(':', $line, 2);
                $responseHeaders[$key] = $value;
            }
            curl_close($curl);
            if (isset($buffer) && is_resource($buffer)) {
                fclose($buffer);
            }
            return new Response($statusCode, $body, $responseHeaders);
        } catch (\ErrorException $e) {
            if (isset($curl) && is_resource($curl)) {
                curl_close($curl);
            }
            if (isset($buffer) && is_resource($buffer)) {
                fclose($buffer);
            }
            throw $e;
        }
    }

    public function options($method, $url, $params = array(), $data = array(),
                            $headers = array(), $user = null, $password = null,
                            $timeout = null)
    {
        $timeout = is_null($timeout)
            ? self::DEFAULT_TIMEOUT
            : $timeout;
        $options = $this->curlOptions + array(
                CURLOPT_URL => $url,
                CURLOPT_HEADER => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_INFILESIZE => -1,
                CURLOPT_HTTPHEADER => array('User-Agent: wilddog-sms-php/1.0.3'),
                CURLOPT_TIMEOUT => $timeout,
                CURLOPT_SSL_VERIFYPEER => false
            );
        foreach ($headers as $key => $value) {
            $options[CURLOPT_HTTPHEADER][] = "$key: $value";
        }
        if ($user && $password) {
            $options[CURLOPT_HTTPHEADER][] = 'Authorization: Basic ' . base64_encode("$user:$password");
        }
        $body = $this->buildQuery($params);

        switch (strtolower(trim($method))) {
            case 'get':
                if ($body) {
                    $options[CURLOPT_URL] .= '?' . $body;
                }
                $options[CURLOPT_HTTPGET] = true;
                break;
            case 'post':
                $options[CURLOPT_POST] = true;
                $options[CURLOPT_HTTPHEADER][] = 'Content-Length: ' . strlen($body);
                $options[CURLOPT_POSTFIELDS] = $body;
                break;
            case 'put':
                $options[CURLOPT_PUT] = true;
                if ($data) {
                    if ($buffer = fopen('php://memory', 'w+')) {
                        $dataString = $this->buildQuery($data);
                        fwrite($buffer, $dataString);
                        fseek($buffer, 0);
                        $options[CURLOPT_INFILE] = $buffer;
                        $options[CURLOPT_INFILESIZE] = strlen($dataString);
                    } else {
                        throw new WilddogException('Unable to open a temporary file');
                    }
                }
                break;
            case 'head':
                $options[CURLOPT_NOBODY] = true;
                break;
            default:
                $options[CURLOPT_CUSTOMREQUEST] = strtoupper($method);
        }

        return $options;
    }

    public function buildQuery($params)
    {
        return is_array($params) ? http_build_query($params) : '';
    }
}