<?php
declare(strict_types=1);

namespace Autoframe\Core\Http\Cookie;

use Autoframe\Core\Entity\AfrEntity;

class AfrHttpCookieEntity extends AfrEntity
{
    public string $sSameSite = '';
    public string $sDomain = '';
    public string $sValue = '';
    public string $sPath = '';
    public bool $nHttponly;
    public bool $bSecure;
    public int $iLifetime;

}