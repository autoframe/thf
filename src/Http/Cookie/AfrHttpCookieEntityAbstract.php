<?php
declare(strict_types=1);

namespace Autoframe\Core\Http\Cookie;

use Autoframe\Core\Http\Cookie\Exception\AfrHttpCookieException;
use Autoframe\Core\Http\Cookie\Manager\AfrHttpCookieSameSiteTrait;
use Autoframe\Core\Http\Request\AfrHttpRequestHttps;

abstract class AfrHttpCookieEntityAbstract
{
    //TODO singleton model cookie manager atunci cand apelez constructor sa salveze indexul; + metoda set si delete

    use AfrHttpCookieSameSiteTrait;
    use AfrHttpRequestHttps;

    /**
     * Cookie name
     * @var string
     */
    public string $sName;

    /**
     * SameSite :  'Lax', 'Strict', 'None', ''
     * @var string
     */
    public string $sSameSite = '';

    /**
     * '.domain.com' for all subdomains
     * 'www.domain.com' for only one subdomain
     * '.' for all auto generated subdomains
     * '' for the current domain
     * @var string
     */
    public string $sDomain = '.';

    /**
     * Cookie payload value. Leave '' for cookie delete
     * @var string
     */
    public string $sValue;

    /**
     * Path '/' for all domain or subdomain
     * @var string
     */
    public string $sPath = '/';

    /**
     * If true, the cookie is not accessible from js
     * @var bool
     */
    public bool $bHttpOnly = true;

    /**
     * HTTPS secure only
     * @var bool
     */
    public bool $bSecure = true;

    /**
     * For browser session cookie, use 0
     * Else time() + seconds into the future
     * @var int
     */
    public int $iLifetime = 0;

    /**
     * @throws AfrHttpCookieException
     */
    final public function __construct(
        string $sName,
        string $sValue,
        int    $iLifetime = 0,
        string $sPath = '/',
        string $sDomain = '.',
        string $sSameSite = 'Strict',
        bool   $bHttpOnly = false,
        bool   $bSecure = true
    )
    {
        $this->sName = $sName;
        $this->sValue = $sValue;
        $this->iLifetime = $iLifetime;
        $this->sPath = $sPath;
        $this->sDomain = $sDomain;
        $this->sSameSite = $sSameSite;
        $this->bHttpOnly = $bHttpOnly;
        $this->bSecure = $bSecure;
        $this->validateCookieSettings();
    }



    /**
     * @return void
     * @throws AfrHttpCookieException
     */
    protected function validateCookieSettings()
    {
        if (empty($this->sName)) {
            throw new AfrHttpCookieException('Cookie name can not be blank in ' . get_class($this));
        }
        if (empty($this->sPath)) {
            $this->sPath = '/';
        }
        if (!in_array($this->sSameSite, $this->getSameSiteOptions())) {
            $this->sSameSite = '';
        }
        if ($this->bSecure && !$this->isHttpsRequest()) {
            $this->bSecure = false;
        }
        if ($this->sDomain === '.') {
            $this->sDomainAutocomplete();
        }
        if ($this->iLifetime < 0) {
            $this->iLifetime = 2; //set to expire
        }

    }

    /**
     * @return void
     */
    private function sDomainAutocomplete(): void
    {
        $sHostName = !empty($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
        if ($sHostName && filter_var($sHostName, FILTER_VALIDATE_IP)) {//ip hostname
            $this->sDomain = '';
        } elseif ($sHostName) {//string hostname
            $aHostName = explode('.', $sHostName);
            if (count($aHostName) > 1) {
                $aHostName = array_slice($aHostName, -2, 2); //remove all but last 2 segments
            }
            $sHostName = '.' . implode('.', $aHostName);
            $this->sDomain = $sHostName;
        } else {
            $this->sDomain = ''; //fallback to blank and let the browser decide
        }
    }

}