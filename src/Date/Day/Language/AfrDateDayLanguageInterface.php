<?php
declare(strict_types=1);

namespace Autoframe\Core\Date\Day\Language;

interface AfrDateDayLanguageInterface
{
    /**
     * @return string[]
     */
    public function getDayNames(): array;

    /**
     * @return string[]
     */
    public function getDayNamesShort(): array;
}