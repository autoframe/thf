<?php
declare(strict_types=1);

namespace Autoframe\Core\Date\Month\Language;

interface AfrDateMonthLanguageInterface
{
    /**
     * @return string[]
     */
    public function getMonthNames(): array;

    /**
     * @return string[]
     */
    public function getMonthNamesShort(): array;
}