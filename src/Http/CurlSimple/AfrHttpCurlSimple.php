<?php
declare(strict_types=1);

namespace Autoframe\Core\Http\CurlSimple;

use Autoframe\Core\Http\CurlSimple\Exception\AfrHttpCurlSimpleException;
use Autoframe\Core\Http\Header\Formatters\Exception\AfrHttpHeaderFormattersException;

use Autoframe\Core\Http\Header\Formatters\AfrHttpHeaderFormatters;
use Autoframe\Core\Http\Header\Formatters\AfrHttpHeaderFormattersCookie;

trait AfrHttpCurlSimple
{
    use AfrHttpHeaderFormatters;
    use AfrHttpHeaderFormattersCookie;
    use AfrHttpCurlSimpleMethods;

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
     * @throws AfrHttpCurlSimpleException
     * @throws AfrHttpHeaderFormattersException
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
    ): array
    {

        $aOptions = [
            'http' => [
                'method' => $sMethod,
                'ignore_errors' => $bIgnoreErrors,
                'follow_location' => $iMaxRedirects ? 1 : 0,
                'max_redirects' => $iMaxRedirects,
                'timeout' => $fTimeoutSeconds,
            ]];

        $aOptionalHeaders = $this->formatMixedHeadersInputToKeyArray($mOptionalHeaders);
        if (count($aOptionalHeaders)) {
            $aOptions['http']['header'] = $this->formatFlattenHeaderArray($aOptionalHeaders);
        }

        if (count($aPostData)) {
            $aOptions['http']['content'] = http_build_query($aPostData);
        }

        $ctx = stream_context_create($aOptions);
        if ($bDebugList) {
            $this->oldPrea($aOptions);
            $this->oldPrea($ctx);
        }
        $fp = @fopen($sUrl, 'rb', false, $ctx);
        if (!$fp) {
            throw new AfrHttpCurlSimpleException('Unable to open stream to: ' . $sUrl);
        }
        $response = stream_get_contents($fp);
        if ($response === false) {
            throw new AfrHttpCurlSimpleException('Unable to get response stream from: ' . $sUrl);
        }
        return ['header' => $http_response_header, 'content' => $response];
    }

    /**
     * @return array
     */
    public function getCurlConstantAliasCodes(): array
    {
        $aAlias = [];
        foreach (get_defined_constants() as $sConstant => $mVal) {
            $sPrefix = 'CURLOPT_';
            $iPrefix = strlen($sPrefix);
            if (substr($sConstant, 0, $iPrefix) === $sPrefix) {
                $aAlias[$mVal] = $sConstant;
            }
        }
        return $aAlias;
    }

    /**
     * @param string $sUrl full url https://...
     * @param string $sMethod  GET, POST, HEAD...
     * @param array $aPostFields  [ k=>v ]
     * @param array $aHeaders  [ 0=>'Afr: curl' | k=>v : 'Accept-Language' => 'en'   ]
     * @param array $aSetOpt  [ CURLOPT_TIMEOUT => 5 , ]
     * @param array|string $saCookies  [ k=>v, ] | lang=en; ts=1669731924
     * @param string $sRef full url https://...
     * @param int $iMaxRedirects  zero, +int or -1 = infinite
     * @param array $aUserPwd  [0=> user, 1=> pwd]
     * @return array used with: $aResult = $this->makeSimpleCurlRequest($aFinalCurlOptions)
     * @throws AfrHttpCurlSimpleException
     * @throws AfrHttpHeaderFormattersException
     */
    protected function prepareSimpleCurlHandle(
        string $sUrl,
        string $sMethod = 'GET', // 'GET','POST','HEAD'...
        array  $aPostFields = [],
        array  $aHeaders = [],
        array  $aSetOpt = [],
               $saCookies = '', // header string || array
        string $sRef = '',
        int    $iMaxRedirects = 0,
        array  $aUserPwd = []
    ): array
    {
        if (count($aPostFields) && $sMethod != 'POST') {
            $sMethod = 'POST';
        }

        $aOrganizedHeaders = $this->prepareCurlOrganizedHeaders($aHeaders);
        $aCurlOptions = $this->prepareCurlOrganizedOptions($aSetOpt);

        if ($sRef) {
            $aOrganizedHeaders['Referer'] = $sRef;
        }

        if ($saCookies) {
            $sCookieHeaderLine = $this->formatIntoCookieHeaderLine($saCookies, false, false);
            if (!empty($aOrganizedHeaders['Cookie'])) {
                $sCookieHeaderLine = rtrim($aOrganizedHeaders['Cookie'], '; ') . '; ' . $sCookieHeaderLine;
                $sCookieHeaderLine = $this->formatIntoCookieHeaderLine($sCookieHeaderLine, false, false);
            }
            $aOrganizedHeaders['Cookie'] = $sCookieHeaderLine;
        }

        if (!$aOrganizedHeaders['Cookie']) {
            unset($aOrganizedHeaders['Cookie']);
        }

        $aFinalCurlOptions = $this->prepareCurlOrganizedOptionsAndHeaders($aCurlOptions, $aOrganizedHeaders, $sMethod, $sUrl);
        $this->prepareCurlOrganizedOptionsSetMaxRedirects($aFinalCurlOptions, $iMaxRedirects);
        $this->prepareCurlOrganizedOptionsSetPostData($aFinalCurlOptions, $aPostFields, $sMethod);
        $this->prepareCurlOrganizedOptionsSetReturnConnectionHeaders($aFinalCurlOptions, true);
        $this->prepareCurlOrganizedOptionsSetUserPwd($aFinalCurlOptions, $aUserPwd);
        return $aFinalCurlOptions;

    }

    /**
     * @param array $aFinalCurlOptions
     * @param bool $bInlineDebug
     * @return string[]
     */
    protected function makeSimpleCurlRequest(array $aFinalCurlOptions, bool $bInlineDebug = false): array
    {
        if($bInlineDebug){
            $aAlias = $this->getCurlConstantAliasCodes();
            $aDebugOptions = [];
            foreach($aFinalCurlOptions as $sK=>$mV){
                $aDebugOptions[isset($aAlias[$sK]) ? $aAlias[$sK] : $sK] = $mV;
            }
            var_dump($aDebugOptions);

        }
        $ch = curl_init();
        curl_setopt_array($ch, $aFinalCurlOptions);

        $response = curl_exec($ch);
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);

        $result = [
            'header' => substr($response, 0, $header_size),
            'body' => substr($response, $header_size),
            'curl_error' => curl_error($ch),
            'http_code' => curl_getinfo($ch, CURLINFO_HTTP_CODE),
            'last_url' => curl_getinfo($ch, CURLINFO_EFFECTIVE_URL)
        ];
        curl_close($ch);

        //	if ($result['curl_error'])    throw new Exception($result['curl_error']);
        //	if ($result['http_code']!='200')    throw new Exception("HTTP Code = ".$result['http_code']);
        //	if (!$result['body'])        throw new Exception("Body of file is empty");
        return $result;
    }

    /**
     * @param string $sUrl
     * @param array $aPost
     * @return void
     * @throws AfrHttpCurlSimpleException
     * @throws AfrHttpHeaderFormattersException
     */
    protected function makeTestRequestTo(
        string $sUrl = 'https://autoframe.ro/',
        array $aPost = []
    )
    {
        $aFinalCurlOptions = $this->prepareSimpleCurlHandle(
            $sUrl,
            $aPost ? 'POST':'GET',
            $aPost,
            [
                'Afr: curl',
                'Accept-Language:'
            ],
            [],
            'defaultP=P_cookie'
        );
        $aResult = $this->makeSimpleCurlRequest($aFinalCurlOptions, true);
        var_dump($aResult);
        die;
    }

}