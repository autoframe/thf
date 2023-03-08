<?php
declare(strict_types=1);

namespace Autoframe\Core\Date\Hours\Language;

interface AfrDateHoursLanguageInterface
{
    /**
     * @return string[]
     */
    public function getHoursNames(): array;

    /**
     * @return string[]
     */
    public function getHoursNamesShort(): array;
}