<?php
declare(strict_types=1);

namespace Autoframe\Core\Http\Cookie;

trait AfrHttpCookie
{
    /**
     * @param string $name
     * @param string $value
     * @param $iExpires_or_aOptions `0 = session; timestamp or arr [lifetime path domain secure httponly 'samesite' => 'Strict|Lax|No0ne']
     * @param string $path
     * @param string $domain
     * @param bool $secure
     * @param bool $httponly
     * @param string $sameSite lax strict or ''
     * @return bool
     */
    public function setCookie(
        string $name,
        string $value = '',
               $iExpires_or_aOptions = 0,
        string $path = '',
        string $domain = '',
        bool   $secure = false,
        bool   $httponly = false,
        string $sameSite = ''
    ): bool
    {
        $lifetime = 0;
        if (is_array($iExpires_or_aOptions)) {
            if (isset($iExpires_or_aOptions['samesite'])) {
                $sameSite = (string)$iExpires_or_aOptions['samesite'];
            }
            if (isset($iExpires_or_aOptions['httponly'])) {
                $httponly = (bool)$iExpires_or_aOptions['httponly'];
            }
            if (isset($iExpires_or_aOptions['secure'])) {
                $secure = (bool)$iExpires_or_aOptions['secure'];
            }
            if (isset($iExpires_or_aOptions['domain'])) {
                $domain = (string)$iExpires_or_aOptions['domain'];
            }
            if (isset($iExpires_or_aOptions['path'])) {
                $path = (string)$iExpires_or_aOptions['path'];
            }
            if (isset($iExpires_or_aOptions['lifetime'])) {
                $lifetime = (int)$iExpires_or_aOptions['lifetime'];
            }
        } else {
            $lifetime = (int)$iExpires_or_aOptions;
        }
        $this->correctSameSiteCookieDirective($sameSite);

        if (strlen($value)) {
            $_COOKIE[$name] = $value;
        } elseif (isset($_COOKIE[$name])) {
            unset($_COOKIE[$name]);
        }

        if (PHP_VERSION_ID >= 70300 && is_array($iExpires_or_aOptions) && $iExpires_or_aOptions) {
            //  setcookie(string $name, $value = '', array $options = []): bool
            return setcookie(
                $name,
                $value,
                $iExpires_or_aOptions
            );
        } else {
            //  setcookie(string $name, $value = '', $expires_or_options = 0, $path = '', $domain = '', $secure = false, $httponly = false): bool
            return setcookie(
                $name,
                $value,
                $lifetime,
                $path . ($sameSite ? '; samesite=' . $sameSite : ''),
                $domain,
                $secure,
                $httponly
            );
        }
    }

    /**
     * @param string $name
     * @param string $path
     * @param string $domain
     * @param bool $secure
     * @param bool $httponly
     * @param string $samesite
     * @return bool
     */
    public function deleteCookie(string $name,
                                 string $path = '',
                                 string $domain = '',
                                 bool   $secure = false,
                                 bool   $httponly = false,
                                 string $samesite = ''
    ): bool
    {
        return $this->setCookie($name, '', 1, $path, $domain, $secure, $httponly, $samesite);
    }


    /**
     * Input parameter is forced to Lax Strict None or ''
     * @param $sameSite
     * @return void
     */
    private function correctSameSiteCookieDirective(&$sameSite): void
    {
        if (is_string($sameSite) && in_array(strtolower($sameSite), ['lax', 'strict', 'none'])) {
            $sameSite = ucwords($sameSite);
        } else {
            $sameSite = '';
        }
    }
}