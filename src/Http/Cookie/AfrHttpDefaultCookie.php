<?php
declare(strict_types=1);

namespace Autoframe\Core\Http\Cookie;

trait AfrHttpDefaultCookie
{
    use AfrHttpCookie;

    private string $sAfrHttpDefaultCookiePath = '/';
    private string $sAfrHttpDefaultCookieDomain = ''; //$_SERVER['HTTP_HOST'];  $this->getRequestSchemeHostPort
    private string $sAfrHttpDefaultCookieSameSite = '';
    private string $sAfrHttpDefaultCookieSecure = 'isHttpsRequest';
    private string $sAfrHttpDefaultCookieHttpOnly = 'true';

}