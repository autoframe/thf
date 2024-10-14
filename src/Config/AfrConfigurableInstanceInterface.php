<?php
declare(strict_types=1);

namespace Autoframe\Core\Config;

interface AfrConfigurableInstanceInterface
{
    /**
     * @param bool $bForce
     * @return int
     */
    public function applyAfrInstanceConfig(bool $bForce = false): int;

    /**
     * @param bool $bForce
     * @return int
     */
    public static function applyAfrInstanceConfigStatic(bool $bForce = false): int;

}