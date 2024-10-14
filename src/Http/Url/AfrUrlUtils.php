<?php

namespace Autoframe\Core\Http\Url;

trait AfrUrlUtils
{

    /**
     * @param string $user
     * @param string $pass
     * @param string $link
     * @return false|string
     */
    public function getNtlmLinkContents(string $user, string $pass, string $link)
    {
        return file_get_contents(urlencode($user) . '@' . urlencode($pass) . ':' . $link);
    }

    /**
     * @param string $sUrl
     * @return bool
     */
    public function isUrlSecure(string $sUrl): bool
    {
        return strtolower(substr($sUrl, 0, 6)) === 'https:';
    }

    /**
     * @param string $sUrl
     * @return string https://username:password@hostname:9090
     */
    public function getUrlSchemeHostUpToPath(string $sUrl): string
    {
        $aParts = explode('/', $sUrl, 0, 3);
        return implode('/', array_slice($aParts, 0, 3));
    }


}