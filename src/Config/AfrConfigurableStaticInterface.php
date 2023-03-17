<?php
declare(strict_types=1);

namespace Autoframe\Core\Config;

interface AfrConfigurableStaticInterface
{
    /**
     * @param bool $bForce
     * @return int
     */
    public static function applyAfrStaticConfig(bool $bForce = false): int;
}