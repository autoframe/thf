<?php

namespace Autoframe\Core\Http\Url;

trait AfrUrlUtils
{


    public function get_ntlm_link($user, $pass, $link)
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



}