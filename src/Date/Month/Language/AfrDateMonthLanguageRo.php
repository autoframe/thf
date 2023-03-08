<?php
declare(strict_types=1);

namespace Autoframe\Core\Date\Month\Language;

class AfrDateMonthLanguageRo implements AfrDateMonthLanguageInterface
{
    /**
     * @return string[]
     */
    public function getMonthNames(): array
    {
        return [
            'Decembrie',
            'Ianuarie',
            'Februarie',
            'Martie',
            'Aprilie',
            'Mai',
            'Iunie',
            'Iulie',
            'August',
            'Septembrie',
            'Octombrie',
            'Noiembrie',
            'Decembrie'
        ];
    }

    /**
     * @return string[]
     */
    public function getMonthNamesShort(): array
    {
        return [
            'Dec',
            'Ian',
            'Feb',
            'Mar',
            'Apr',
            'Mai',
            'Iun',
            'Iul',
            'Aug',
            'Sep',
            'Oct',
            'Nov',
            'Dec'
        ];
    }
}