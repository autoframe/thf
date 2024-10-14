<?php
declare(strict_types=1);

namespace Autoframe\Core\Date\Day\Language;

class AfrDateDayLanguageRo implements AfrDateDayLanguageInterface
{
    /**
     * @return string[]
     */
    public function getDayNames(): array
    {
        return ['Duminică', 'Luni', 'Marţi', 'Miercuri', 'Joi', 'Vineri', 'Sâmbată', 'Duminică'];
    }

    /**
     * @return string[]
     */
    public function getDayNamesShort(): array
    {
        return ['Du', 'Lu', 'Ma', 'Mi', 'Jo', 'Vi', 'Sâ', 'Du'];
    }
}