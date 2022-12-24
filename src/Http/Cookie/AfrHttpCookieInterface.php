<?php
declare(strict_types=1);

namespace Autoframe\Core\Http\Cookie;

interface AfrHttpCookieInterface
{
    /**
     * @param string $name
     * @param string $value
     * @param $iExpires_or_aOptions `0 = session; timestamp or arr [lifetime path domain secure httponly 'samesite' => 'Strict|Lax']
     * @param string $path
     * @param string $domain
     * @param bool $secure
     * @param bool $httponly
     * @param string $sameSite lax strict or ''
     * @return bool
     */
    public function setCookie(string $name, string $value = '', $iExpires_or_aOptions = 0, string $path = '', string $domain = '', bool $secure = false, bool $httponly = false, string $sameSite = ''): bool;

    /**
     * @param string $name
     * @param string $path
     * @param string $domain
     * @param bool $secure
     * @param bool $httponly
     * @param string $samesite
     * @return bool
     */
    public function deleteCookie(string $name, string $path = '', string $domain = '', bool $secure = false, bool $httponly = false, string $samesite = ''): bool;
}