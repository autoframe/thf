<?php

namespace Autoframe\Core\Http\Request;

trait AfrHttpRequest
{
    /**
     * @return array
     */
    public function getServerRequestHeaders(): array
    {
        if (function_exists('apache_request_headers')) {
            $aHeaders = apache_request_headers();
            if (empty($aHeaders)) {
                $aHeaders = [];
            }
        } else {
            $aHeaders = $this->httpReadHeadersFromDollarServer();
        }
        return $aHeaders;
    }

    /**
     * @return array
     */
    private function httpReadHeadersFromDollarServer(): array
    {
        $aHeaders = [];
        $sPrefix = 'HTTP_';
        $iPrefixLen = strlen($sPrefix);
        foreach ($_SERVER as $sServerKey => $sValue) {
            if (substr($sServerKey, 0, $iPrefixLen) === $sPrefix) {
                $sHeaderName = substr($sServerKey, $iPrefixLen);
                // do some nasty string manipulations to restore the original letter case
                // this should work in most cases
                $aHeaderNameParts = explode('_', trim($sHeaderName, ' _-'));
                if (count($aHeaderNameParts)) {
                    foreach ($aHeaderNameParts as $ak_key => $ak_val) {
                        $aHeaderNameParts[$ak_key] = ucfirst($ak_val);
                    }
                    $sHeaderName = implode('-', $aHeaderNameParts);
                }
                $aHeaders[$sHeaderName] = $sValue;
            }
        }
        return $aHeaders;
    }


    public function getHttpRequested(
        bool $bBody = false,
        bool $bHeaders = false,
        bool $bServer = false,
        bool $bSes = false,
        bool $bEnv = false,
        bool $bGlobals = false
    ): array
    {
        $sRqMethod = $_SERVER['REQUEST_METHOD']; //GET, HEAD, POST, PUT, PATCH, CONNECT, DELETE, OPTIONS, TRACE
        return [
            'php_sapi_name' => php_sapi_name(),
            'REQUEST_METHOD' => $sRqMethod,
            'REQUEST_URI' => $_SERVER['REQUEST_URI'],
            'headers' => $bHeaders ? $this->getServerRequestHeaders() : null,
            'superglobals' => [
                '$_GET' => $_GET,
                '$_POST' => $_POST,
                '$_COOKIE' => $_COOKIE,
                '$_FILES' => $_FILES,
                '$_REQUEST' => $_REQUEST,
                '$_SERVER' => $bServer ? $_SERVER : null,
                '$_SESSION' => $bSes && !empty($_SESSION) ? $_SESSION : null,
                '$_ENV' => $bEnv ? $_ENV : null,
                '$GLOBALS' => $bGlobals ? $GLOBALS : null,
            ],
            'body' => $bBody ? file_get_contents("php://input") : null,
        ];
    }

    /**
     * @return bool
     */
    public function isHttpsRequest(): bool
    {
        $bIsSecure = false;
        if (
            (!empty($_SERVER['REQUEST_SCHEME']) && $_SERVER['REQUEST_SCHEME'] == 'https') ||
            (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ||
            (!empty($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443') ||
            (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) == 'https') ||
            (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && strtolower($_SERVER['HTTP_X_FORWARDED_SSL']) == 'on')
        ) {
            $bIsSecure = true;
        }
        return $bIsSecure;
    }

    /**
     * @return string
     */
    public function getRequestSchemeHostPort(): string
    {
        if ($this->isCli()) {
            return php_sapi_name();
        }
        $iPort = !empty($_SERVER['SERVER_PORT']) ? $_SERVER['SERVER_PORT'] : 0;
        if ($this->isHttpsRequest() == 'https') {
            return 'https://' . $_SERVER['HTTP_HOST'] . ($iPort == 443 ? '' : ':' . $iPort);
        } else {
            return 'http://' . $_SERVER['HTTP_HOST'] . ($iPort == 80 ? '' : ':' . $iPort);
        }
    }

    /**
     * @return bool
     */
    public function isCli():bool
    {
        return !(strpos(strtolower(php_sapi_name()), 'cli') === false);
    }

}