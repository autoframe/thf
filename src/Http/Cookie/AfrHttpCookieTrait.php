<?php
declare(strict_types=1);

namespace Autoframe\Core\Http\Cookie;

use Autoframe\Core\Http\Cookie\Exception\AfrHttpCookieException;
use Autoframe\Core\Http\Request\AfrHttpRequestHttps;
use Autoframe\Core\Http\Cookie\Manager\AfrHttpCookieManagerClass;

trait AfrHttpCookieTrait
{
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
    public bool $bHttpOnly = false;

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

    public bool $bAssumed = false;

    /**
     * @throws AfrHttpCookieException
     */
    final public function __construct(
        string $sName,
        string $sValue,
        int    $iLifetime,
        string $sPath = '/',
        string $sDomain = '.',
        string $sSameSite = 'Strict',
        bool   $bHttpOnly = true,
        bool   $bSecure = true
    )
    {
        $oCm = $this->getCookieManager();

        $cname = $oCm->fixCookieName($sName);
        if (strlen($cname) < 1) {
            throw new AfrHttpCookieException('Invalid cookie name: "' . $sName . '"');
        }
        $this->sName = $cname;
        $this->sValue = $sValue;
        $this->iLifetime = $iLifetime;
        $this->sPath = ($sPath ? $sPath : $oCm->assumePath); # '/'
        $this->sDomain = $sDomain;
        $this->sSameSite = $sSameSite;
        $this->bHttpOnly = $bHttpOnly;
        $this->bSecure = $bSecure;
        $this->validateCookieSettings();
    }

    /**
     * @return AfrHttpCookieManagerClass
     */
    public function getCookieManager(): AfrHttpCookieManagerClass
    {
        return AfrHttpCookieManagerClass::getInstance();
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
        if ($this->bSecure && !$this->isHttpsRequest()) {
            $this->bSecure = false;
        }
        if (!$this->bSecure || !in_array($this->sSameSite, $this->getCookieManager()->getSameSiteOptions())) {
            $this->sSameSite = '';
        }

        if ($this->sDomain === '.') {
            $this->sDomain = $this->getCookieManager()->domainNameAutodetect();
        }
        if ($this->iLifetime < 0) {
            $this->iLifetime = 2; //set to expire
        }

    }

    /**
     * @return bool
     */
    public function set(): bool
    {
        $oCm = $this->getCookieManager();
        $bResponse = $oCm->setCookie(
            $this->sName,
            $this->sValue,
            $this->iLifetime,
            $this->sPath,
            $this->sDomain,
            $this->bSecure,
            $this->bHttpOnly,
            $this->sSameSite,
        );
        if ($bResponse) {
            $oCm->setIndex($this);
        }
        return $bResponse;
    }

    /**
     * @return bool
     */
    public function setIfMissing(): bool
    {
        if(!isset($_COOKIE[$this->sName])){
            return $this->set();
        }
        return $this->getCookieManager()->issetIndex($this->sName);
    }

    /**
     * @return bool
     */
    public function unset(): bool
    {
        return $this->getCookieManager()->unsetCookie(
            $this->sName,
            $this->sPath,
            $this->sDomain,
            $this->bSecure,
            $this->bHttpOnly,
            $this->sSameSite,
        );
    }

}