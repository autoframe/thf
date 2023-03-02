<?php
declare(strict_types=1);

namespace Autoframe\Core\Http\Cookie\Manager;

use Autoframe\Core\Object\AfrObjectSingletonAbstractClass;

class AfrHttpCookieManagerManagerClass extends AfrObjectSingletonAbstractClass  implements AfrHttpCookieManagerInterface
{
    use AfrHttpCookieTrait;

    /**
     * @return AfrHttpCookieManagerManagerClass
     */
    public static function getInstance(): AfrHttpCookieManagerManagerClass
    {
        return parent::getInstance();
    }
}