<?php

namespace Happyr\LinkedIn\Http;

use Happyr\LinkedIn\Exceptions\LinkedInApiException;

/**
 * Class Request
 *
 * Makes an HTTP request with curl
 *
 * @author Tobias Nyholm
 *
 */
class CurlRequest implements RequestInterface
{
    /**
     * Default options for curl.
     */
    public static $curlOptions = array(
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 60,
        CURLOPT_USERAGENT      => 'linkedin-php-client',
    );

    /**
     * {@inheritdoc}
     *
     * This method can be overridden by subclasses if
     * developers want to do fancier things or use something other than curl to
     * make the request.
     *
     */
    public function send($url, $params = array(), $method = 'GET', $contentType = null)
    {
        $opts = $this->prepareParams($url, $params, $method, $contentType);

        $ch = curl_init();
        curl_setopt_array($ch, $opts);
        $result = curl_exec($ch);

        if ($result === false) {
            $e = new LinkedInApiException(
                array(
                    'error_code' => curl_errno($ch),
                    'error' => array(
                        'message' => curl_error($ch),
                        'type' => 'CurlException',
                    ),
                )
            );

            curl_close($ch);
            throw $e;
        }

        curl_close($ch);

        return $result;
    }

    /**
     * Prepare Curl parameters
     *
     * @param string $url
     * @param array $params
     * @param string $method
     * @param string $contentType
     *
     * @return array
     */
    protected function prepareParams($url, $params, $method, $contentType)
    {
        $opts = self::$curlOptions;
        $opts[CURLOPT_POST] = strtoupper($method) == 'POST';
        if ($opts[CURLOPT_POST]) {
            if ($contentType == 'json') {
                $opts[CURLOPT_POSTFIELDS] = is_string($params) ? $params : json_encode($params);
            } elseif ($contentType == 'xml') {
                $opts[CURLOPT_POSTFIELDS] = is_string($params) ? $params : $params->asXML();
            } else {
                $opts[CURLOPT_POSTFIELDS] = http_build_query($params, null, '&');
            }
        }

        $opts[CURLOPT_URL] = $url;

        // disable the 'Expect: 100-continue' behaviour. This causes CURL to wait
        // for 2 seconds if the server does not support this header.
        $opts[CURLOPT_HTTPHEADER] = array('Expect:');

        if ($contentType) {
            $mimeType = $contentType == 'xml' ? 'text/xml' : 'application/json';
            $opts[CURLOPT_HTTPHEADER][] = "Content-Type: {$mimeType}";
        }

        return $opts;
    }
}