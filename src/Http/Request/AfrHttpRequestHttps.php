<?php
declare(strict_types=1);

namespace Autoframe\Core\Http\Request;

trait AfrHttpRequestHttps
{
    /**
     * @return bool
     */
    protected function isHttpsRequest(): bool
    {
        $bIsSecure = false;
        if (
            (!empty($_SERVER['REQUEST_SCHEME']) && strtolower($_SERVER['REQUEST_SCHEME']) === 'https') ||
            (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) === 'on') ||
            (!empty($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443) ||
            (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https') ||
            (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && strtolower($_SERVER['HTTP_X_FORWARDED_SSL']) === 'on')
        ) {
            $bIsSecure = true;
        }
        return $bIsSecure;
    }

}