<?php
declare(strict_types=1);

namespace Autoframe\Core\Http\Cookie\Manager;

use Autoframe\Core\Http\Cookie\AfrHttpCookieEntityAbstract;
use Autoframe\Core\Http\Request\AfrHttpRequestHttps;
//use Autoframe\Core\Object\AfrObjectSingletonTrait;

trait AfrHttpCookieTrait
{
    use AfrHttpCookieSameSiteTrait;
    use AfrHttpRequestHttps;
//    use AfrObjectSingletonTrait;

    public bool $bUseDomainDotNotationForAllSubdomains = true;
    protected static array $aIndex;

    /**
     * @param string $name
     * @param string $value
     * @param $iExpires_or_aOptions `0 = session; timestamp or arr [lifetime path domain secure httponly 'samesite' => 'Strict|Lax|None']
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

        if ($this->bUseDomainDotNotationForAllSubdomains) {
            if (
                $domain &&
                substr($domain, 0, 1) !== '.' &&
                !filter_var(explode(':', $domain)[0], FILTER_VALIDATE_IP)
            ) {
                $domain = '.' . $domain;
            }
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
     * @param string $sSameSite
     * @return void
     */
    private function correctSameSiteCookieDirective(string &$sSameSite): void
    {
        $sSameSite = ucwords(strtolower($sSameSite));
        if (!in_array($sSameSite, $this->getSameSiteOptions())) {
            $sSameSite = '';
        }
    }

    /**
     * @param array $aNames
     * @param $asPaths  ['/','/myaccount/']
     * @param $asDomains ['.example.com']
     * @param string $sSameSite Strict|Lax|None|''
     * @param int $iMaxLimit
     * @return int
     */
    public function forceExpireAllCookies(
        array  $aNames = [],
               $asPaths = null,
               $asDomains = null,
        string $sSameSite = '',
        int    $iMaxLimit = 20
    ): int
    {
        if (empty($aNames) && !empty($_COOKIE)) {
            $aNames = array_keys($_COOKIE);
        }
        shuffle($aNames);
        $i = 0;
        foreach ($aNames as $sCookieName) {
            if ($i >= $iMaxLimit) {
                return $i;
            }
            $i += $this->forceExpireCookie($sCookieName, $asPaths, $asDomains, $sSameSite, $iMaxLimit);
        }
        return $i;
    }


    /**
     * @param string $sName
     * @param $asPaths  ['/','/myaccount/']
     * @param $asDomains ['.example.com']
     * @param string $sSameSite Strict|Lax|None|''
     * @param int $iMaxLimit
     * @return int
     */
    public function forceExpireCookie(
        string $sName,
               $asPaths = null,
               $asDomains = null,
        string $sSameSite = '',
        int    $iMaxLimit = 20
    ): int
    {
        $bSecure = $this->isHttpsRequest();
        $this->correctSameSiteCookieDirective($sSameSite);

        if (is_string($asPaths) && $asPaths) {
            $asPaths = [$asPaths];
        }
        if (empty($asPaths)) {
            $asPaths = $this->getPathVariations();
        }


        if (is_string($asDomains)) {
            $asDomains = [$asDomains];
        }
        if (empty($asDomains)) {
            $asDomains = $this->getDomainVariations();
        }
        $i = 0;
        foreach ($asDomains as $sDomain) {
            foreach ($asPaths as $sPath) {
                $this->deleteCookie(
                    $sName,
                    $sPath,
                    $sDomain,
                    $bSecure,
                    false,
                    $sSameSite
                );
                $i++;
                if ($i >= $iMaxLimit) {
                    return $i;
                }
            }
        }
        return $i;
    }

    /**
     * @return string[]
     */
    protected function getPathVariations(): array
    {
        $sPath = !empty($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';

        $aPathParts = ['/'];
        $sPath = explode('?', $sPath)[0];
        if (substr($sPath, -1, 1) === '/') {
            $sPath .= 'virtualFileToThreatCookieDirsCorrectly.txt';
        }
        $aPathInfo = pathinfo($sPath);
        if (!empty($aPathInfo['dirname'])) {
            $sPath = trim($aPathInfo['dirname'], '\/');
        } else {
            $sPath = '';
        }
        $sWalkThrough = '/';
        foreach (explode('/', $sPath) as $sPathPart) {
            if ($sPathPart) {
                $sWalkThrough .= $sPathPart . '/';
                $aPathParts[] = $sWalkThrough;
            }
        }
        return $aPathParts;
    }

    /**
     * @return array|string[]
     */
    protected function getDomainVariations(): array
    {
        $sFullHostname = !empty($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
        if (!$sFullHostname || filter_var(explode(':', $sFullHostname)[0], FILTER_VALIDATE_IP)) {
            return [''];
        }
        $aHostNames = [];
        $aFullHostname = explode('.', trim($sFullHostname, '.'));
        $iCountHostParts = count($aFullHostname);
        if ($iCountHostParts > 1) {
            $sWalkThrough = '.' . $aFullHostname[$iCountHostParts - 1];
            for ($i = $iCountHostParts - 2; $i >= 0; $i--) {
                $sWalkThrough = $aFullHostname[$i] . $sWalkThrough;
                if (!$this->bUseDomainDotNotationForAllSubdomains) {
                    $aHostNames[] = $sWalkThrough;
                }
                $sWalkThrough = '.' . $sWalkThrough;
                if ($this->bUseDomainDotNotationForAllSubdomains) {
                    $aHostNames[] = $sWalkThrough;
                }
            }
        }
        $aHostNames[] = $sFullHostname;
        $aHostNames = array_unique($aHostNames);
        krsort($aHostNames);
        return $aHostNames;
    }

    /**
     * @param AfrHttpCookieEntityAbstract $oCookie
     * @return bool
     */
    public function set(AfrHttpCookieEntityAbstract $oCookie): bool
    {
        self::$aIndex[$oCookie->sName] = $oCookie;
        return $this->setCookie(
            $oCookie->sName,
            $oCookie->sValue,
            $oCookie->iLifetime,
            $oCookie->sPath,
            $oCookie->sDomain,
            $oCookie->bSecure,
            $oCookie->bHttpOnly,
            $oCookie->sSameSite,
        );
    }

}