<?php
declare(strict_types=1);

namespace Autoframe\Core\Date\Hours\Language;

class AfrDateHoursLanguageRo implements AfrDateHoursLanguageInterface
{
    /**
     * @return string[]
     */
    public function getHoursNames(): array
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
    public function getHoursNamesShort(): array
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