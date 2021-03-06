<?php

/**
 * PHP version 5.3
 *
 * @package SaaS\Service\Ticketscloud
 * @author Alex Lushpai <lushpai@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT License
 * @link http://github.com/gwinn/saas-connector
 */

namespace SaaS\Service\Ticketscloud;

use SaaS\Exception\CurlException;
use SaaS\Http\Response;

/**
 * TicketscloudRequest
 *
 * @package SaaS\Service\Ticketscloud
 * @author Alex Lushpai <lushpai@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT License
 * @link http://github.com/gwinn/saas-connector
 */
class Request
{
    const METHOD_GET = 'GET';
    const METHOD_POST = 'POST';
    const METHOD_PUT = 'PUT';
    const METHOD_PATCH = 'PATCH';
    const METHOD_DELETE = 'DELETE';

    private $url;
    private $token;

    /**
     * Request constructor.
     *
     * @param $token
     */
    public function __construct($token)
    {
        $this->url = 'https://ticketscloud.org/v1/';
        $this->token = $token;
    }

    /**
     * Make HTTP request
     *
     * @param string $path
     * @param string $method (default: 'GET')
     * @param array $parameters (default: array()
     *
     * @return array
     */
    public function makeRequest($path, $method, array $parameters = array())
    {
        $allowedMethods = array(
            self::METHOD_GET,
            self::METHOD_POST,
            self::METHOD_PUT,
            self::METHOD_PATCH,
            self::METHOD_DELETE
        );

        if (!in_array($method, $allowedMethods)) {
            throw new \InvalidArgumentException(sprintf(
                'Method "%s" is not valid. Allowed methods are %s',
                $method,
                implode(', ', $allowedMethods)
            ));
        }

        $path = $this->url . $path;

        if (self::METHOD_GET === $method && sizeof($parameters)) {
            $path .= '?' . http_build_query($parameters, '', '&');
        }

        $curlHandler = curl_init();
        curl_setopt($curlHandler, CURLOPT_URL, $path);
        curl_setopt($curlHandler, CURLOPT_HTTPHEADER, array(
            'Accept: application/json',
            'Content-Type: application/json',
            'Authorization: key ' . $this->token,
            'User-Agent: TC-Ticketscloud v.0.6.5'
        ));
        curl_setopt($curlHandler, CURLOPT_TIMEOUT, 180);
        curl_setopt($curlHandler, CURLOPT_CONNECTTIMEOUT, 180);
        curl_setopt($curlHandler, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curlHandler, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curlHandler, CURLOPT_FAILONERROR, false);
        curl_setopt($curlHandler, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curlHandler, CURLOPT_SSL_VERIFYHOST, false);

        if (self::METHOD_POST === $method) {
            curl_setopt($curlHandler, CURLOPT_POST, true);
            curl_setopt($curlHandler, CURLOPT_POSTFIELDS, $parameters);
        }

        $responseBody = curl_exec($curlHandler);
        $statusCode = curl_getinfo($curlHandler, CURLINFO_HTTP_CODE);
        $errno = curl_errno($curlHandler);
        $error = curl_error($curlHandler);

        curl_close($curlHandler);

        if ($errno) {
            throw new CurlException($error, $errno);
        }

        return new Response($statusCode, $responseBody);
    }
}
