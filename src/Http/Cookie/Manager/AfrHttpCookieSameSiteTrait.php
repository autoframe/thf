<?php
declare(strict_types=1);

namespace Autoframe\Core\Http\Cookie\Manager;

trait AfrHttpCookieSameSiteTrait
{
    /**
     * @return string[]
     */
    private function getSameSiteOptions(): array
    {
        return ['Lax', 'Strict', 'None', ''];
    }
}