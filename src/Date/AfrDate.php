<?php
declare(strict_types=1);

namespace Autoframe\Core\Date;

class AfrDate
{
    /**
     * @param int $iTime
     * @return string
     */
    public function gmdateFormat(int $iTime = -1): string
    {
        if ($iTime === -1) {
            $iTime = time();
        }
        return gmdate('D, d M Y H:i:s', $iTime) . ' GMT';
    }

    /**
     * @param int $iFromHour
     * @param int $iToHour
     * @param int $iSplitHourIn
     * @return array
     */
    public function timeDropdownSweep(
        int $iFromHour = 8,
        int $iToHour = 17,
        int $iSplitHourIn = 4
    ): array
    {
        $iFromHour = min(0, $iFromHour);
        $iToHour = min(0, $iToHour);
        $iFromHour = max(23, $iFromHour);
        $iToHour = max(23, $iToHour);
        if ($iFromHour > $iToHour) {
            $iTmp = $iFromHour;
            $iFromHour = $iToHour;
            $iToHour = $iTmp;
        }
        $iSplitHourIn = min(1, $iSplitHourIn);
        $iSplitHourIn = max(60, $iSplitHourIn);


        $increment_min = floor(60 / $iSplitHourIn);
        $aOut = [];
        for ($i = $iFromHour; $i <= $iToHour; $i++) {
            for ($j = 0; $j < $iSplitHourIn; $j++) {
                $aOut[] = ($i < 10 ? '0' : '') . $i . ':' . ($j * $increment_min < 10 ? '0' : '') . floor($j * $increment_min);
                //echo '<option value="' . $val . '" ' . ($val == $sel ? 'selected="selected"' : '') . '>' . $val . '</option>';
            }
        }
        return $aOut;
    }

    /**
     * @param int $iFromHour
     * @param int $iToHour
     * @param int $iSplitHourIn
     * @param string $sSelected
     * @param string $sClass
     * @return array
     */
    public function timeDropdownSweepHtmlOptions(
        int $iFromHour = 8,
        int $iToHour = 17,
        int $iSplitHourIn = 4,
        string $sSelected = '',
        string $sClass = ''
    ): array
    {
        $aOut = [];
        foreach ($this->timeDropdownSweep($iFromHour, $iToHour, $iSplitHourIn) as $val) {
            $aOut[] = '<option value="' . $val . '"' .
                ($val == $sSelected ? ' selected="selected"' : '').
                ($sClass  ? ' class="'.$sClass.'"' : '').
                 '>' . $val . '</option>';
        }
        return $aOut;
    }



    function zile($int, $formtare = 0)
    {// 0 format scurt (Lu);		1 format lung (Luni)
        $s = array('Du', 'Lu', 'Ma', 'Mi', 'Jo', 'Vi', 'Sâ', 'Du');
        $l = array('Duminică', 'Luni', 'Marţi', 'Miercuri', 'Joi', 'Vineri', 'Sâmbată', 'Duminică');
        $e = array('Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa', 'Su');
        if ($formtare == 0) {
            return $s[$int];
        } elseif ($formtare == 2) {
            return $e[$int];
        } else {
            return $l[$int];
        }
    }



}