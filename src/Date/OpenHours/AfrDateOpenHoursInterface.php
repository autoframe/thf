<?php
declare(strict_types=1);

namespace Autoframe\Core\Date\OpenHours;

interface AfrDateOpenHoursInterface
{
    /**
     * @param int $iFromHour
     * @param int $iToHour
     * @param int $iSplitHourIn
     * @return array
     */
    public function openHoursValueSweep(int $iFromHour = 8, int $iToHour = 17, int $iSplitHourIn = 4): array;

    /**
     * @param int $iFromHour
     * @param int $iToHour
     * @param int $iSplitHourIn
     * @param string $sSelected
     * @param string $sClass
     * @return array
     */
    public function openHoursHtmlOptionsSweep(int $iFromHour = 8, int $iToHour = 17, int $iSplitHourIn = 4, string $sSelected = '', string $sClass = ''): array;
}