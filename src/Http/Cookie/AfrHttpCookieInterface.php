<?php

namespace Autoframe\Core\Http\Cookie;

use Autoframe\Core\Http\Cookie\Manager\AfrHttpCookieManagerClass;

interface AfrHttpCookieInterface
{
    /**
     * @return AfrHttpCookieManagerClass
     */
    public function getCookieManager(): AfrHttpCookieManagerClass;

    /**
     * @return bool
     */
    public function set(): bool;

    /**
     * @return bool
     */
    public function setIfMissing(): bool;

    /**
     * @return bool
     */
    public function unset(): bool;
}