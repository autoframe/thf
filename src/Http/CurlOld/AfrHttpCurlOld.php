<?php

namespace Autoframe\Core\Http\CurlOld;

use Autoframe\Core\Http\CurlOld\Exception\AfrHttpCurlOldException;

trait AfrHttpCurlOld
{

    /**
     * @param string $sUrl
     * @param string $sMethod
     * @param array $aPostData
     * @param $mOptionalHeaders
     * @param float $fTimeoutSeconds
     * @param bool $bIgnoreErrors
     * @param int $iMaxRedirects
     * @param bool $bDebugList
     * @return array
     * @throws AfrHttpCurlOldException
     */
    public function doHttpStreamRequest(
        string $sUrl,
        string $sMethod = 'POST',
        array  $aPostData = [],
               $mOptionalHeaders = NULL,
        float  $fTimeoutSeconds = 60,
        bool   $bIgnoreErrors = true,
        int    $iMaxRedirects = 0,
        bool   $bDebugList = false
    ):array
    {

        $aOptions = array(
            'http' => array(
                'method' => $sMethod,
                'ignore_errors' => $bIgnoreErrors,
                'follow_location' => $iMaxRedirects ? 1 : 0,
                'max_redirects' => $iMaxRedirects,
                'timeout' => $fTimeoutSeconds,
            ));

        $aOptionalHeaders = [];
        if (is_string($mOptionalHeaders) && strlen($mOptionalHeaders)) {
            $mOptionalHeaders = str_replace("\r", '', $mOptionalHeaders);
            $mOptionalHeaders = explode("\n", $mOptionalHeaders);
        }
        if (is_array($mOptionalHeaders) && count($mOptionalHeaders)) {
            foreach ($mOptionalHeaders as $mKey => $sVal) {
                if (is_numeric($mKey)) {
                    $aOptionalHeaders[] = $sVal;
                } else {
                    $aOptionalHeaders[] = rtrim($mKey, ': ') . ': ' . trim($sVal);
                }
            }
        }

        if (count($aPostData)) {
            $aOptions['http']['content'] = http_build_query($aPostData);
        }
        if (count($aOptionalHeaders)) {
            // "Accept-language: en\r\n" . "Cookie: foo=bar\r\n"
            $aOptions['http']['header'] = implode("\r\n", $aOptionalHeaders);
        }

        $ctx = stream_context_create($aOptions);
        if ($bDebugList) {
            $this->oldPrea($aOptions);
            $this->oldPrea($ctx);
        }
        $fp = @fopen($sUrl, 'rb', false, $ctx);
        if (!$fp) {
            throw new AfrHttpCurlOldException('Unable to open stream to: ' . $sUrl);
        }
        $response = stream_get_contents($fp);
        if ($response === false) {
            throw new AfrHttpCurlOldException('Unable to get response stream from: ' . $sUrl);
        }
        return array('header' => $http_response_header, 'content' => $response);
    }

    private function oldPrea($mixed)
    {
        echo '<pre>' . print_r($this->oldH($mixed), true) . '</pre>';
    }

    private function oldH($str, $enc = 'UTF-8')
    {
        if (is_array($str)) {
            foreach ($str as $key => &$val) {
                $val = $this->oldH($val);
            }
        } elseif(is_string($str)) {
            $str = @htmlentities($str, ENT_QUOTES, $enc);
        }
        return $str;
    }


    public function curl_header_send($url, $method = 'GET', $coookies = NULL, $post_content = NULL, $follow_location = 0, $list_sent_headers = 0, $ref = '')
    {
        $params = array(
            'url' => $url, //'http://google.ro'
            'host' => '',
            'header' => '',
            'method' => $method, // 'GET','POST','HEAD'
            'referer' => $ref,
            'cookie' => $coookies, // data1=abcd; data2=efgh;
            'post_fields' => $post_content, // 'var1=value&var2=value
            //		['login' => '',]
            //      ['password' => '',]
            'timeout' => 20);//max 20 sec exec
        //		prea($params);
        return make_curl_request($params, $follow_location, $list_sent_headers);
    }

    public function make_curl_request($params, $follow_location = 0, $list_sent_headers = 0)
    {
        /*    $params = array('url' => 'http://www.google.com',
         *                    'host' => '',
         *                   'header' => '',
         *                   'method' => 'GET', // 'POST','HEAD'
         *                   'referer' => '',
         *                   'cookie' => '', // data1=abcd; data2=efgh;
         *                   'post_fields' => '', // 'var1=value&var2=value
         *                    ['login' => '',]
         *                    ['password' => '',]
         *                   'timeout' => 20
         *                   );
         */
        $ch = curl_init();
        $user_agent = 'User-Agent: Mozilla/5.0 (Windows NT 6.1; rv:26.0) Gecko/20100101 Firefox/26.0';
        $header = array(
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Language: en-US,en;q=0.5',
            'Accept-Charset: ISO-8859-1,UTF-8;q=0.7,*;q=0.7',
            'Cache-Control: max-age=0',
            'Connection: keep-alive',
        );

        if (isset($params['host']) && $params['host']) $header[] = "Host: " . $params['host'];
        if (isset($params['header']) && $params['header']) $header[] = $params['header'];

        @curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        @curl_setopt($ch, CURLOPT_VERBOSE, 1);
        @curl_setopt($ch, CURLOPT_HEADER, 1);

        if ($params['method'] == "HEAD") {
            @curl_setopt($ch, CURLOPT_NOBODY, 1);
        }
        @curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $follow_location);
        @curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        if ($params['referer']) {
            @curl_setopt($ch, CURLOPT_REFERER, $params['referer']);
        }
        @curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);
        if (isset($params['cookie']) && strlen($params['cookie']) > 0) {
            @curl_setopt($ch, CURLOPT_COOKIE, $params['cookie']);
        }
        if ($params['method'] == "POST") {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params['post_fields']);
        }
        @curl_setopt($ch, CURLOPT_URL, $params['url']);
        @curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        @curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        if (isset($params['login']) && isset($params['password'])) {
            @curl_setopt($ch, CURLOPT_USERPWD, $params['login'] . ':' . $params['password']);
        }
        @curl_setopt($ch, CURLOPT_TIMEOUT, $params['timeout']);
        //////////////////////////////////////////////////////////


        $response = curl_exec($ch);
        $error = curl_error($ch);
        $result = array('header' => '',
            'body' => '',
            'curl_error' => '',
            'http_code' => '',
            'last_url' => '');
        if ($error != "") {
            $result['curl_error'] = $error;
            return $result;
        }

        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $result['header'] = substr($response, 0, $header_size);
        $result['body'] = substr($response, $header_size);
        $result['http_code'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $result['last_url'] = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);


        //	if ($result['curl_error'])    throw new Exception($result['curl_error']);
        //	if ($result['http_code']!='200')    throw new Exception("HTTP Code = ".$result['http_code']);
        //	if (!$result['body'])        throw new Exception("Body of file is empty");
        return $result;
    }


}