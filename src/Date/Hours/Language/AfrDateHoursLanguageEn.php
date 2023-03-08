<?php
declare(strict_types=1);

namespace Autoframe\Core\Date\Hours\Language;

class AfrDateHoursLanguageEn implements AfrDateHoursLanguageInterface
{
    /**
     * @return string[]
     */
    public function getHoursNames(): array
    {
        return [
            'December',
            'January',
            'February',
            'March',
            'April',
            'May',
            'June',
            'July',
            'August',
            'September',
            'October',
            'November',
            'December'
        ];
    }

    /**
     * @return string[]
     */
    public function getHoursNamesShort(): array
    {
        return [
            'Dec',
            'Jan',
            'Feb',
            'Mar',
            'Apr',
            'May',
            'Jun',
            'Jul',
            'Aug',
            'Sep',
            'Oct',
            'Nov',
            'Dec'
        ];
    }
}