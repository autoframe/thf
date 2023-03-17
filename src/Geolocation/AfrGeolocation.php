<?php
declare(strict_types=1);

namespace Autoframe\Core\Geolocation;

class AfrGeolocation
{
    /**
     * Calculeaza distanta in metri, intre doua puncte GPS
     *
     * @param float $fLongitudine1
     * @param float $fLatitudine1
     * @param float $fLongitudine2
     * @param float $fLatitudine2
     * @return float
     */
    public function getGpsDistanceInMeters(float $fLongitudine1, float $fLatitudine1, float $fLongitudine2, float $fLatitudine2)
    {
        //M_PI
        $iRadius = 6371000;
        $fHalfPi = M_PI / 180;
        $fLa1 = $fLatitudine1 * $fHalfPi;
        $fLa2 = $fLatitudine2 * $fHalfPi;
        $fLo1 = $fLongitudine1 * $fHalfPi;
        $fLo2 = $fLongitudine2 * $fHalfPi;

        $f1 = cos($fLa1) * cos($fLa2) * cos($fLo1) * cos($fLo2);
        $f2 = cos($fLa1) * cos($fLa2) * sin($fLo1) * sin($fLo2);
        $f3 = sin($fLa1) * sin($fLa2);

        if (($f1 + $f2 + $f3) >= 1 || ($f1 + $f2 + $f3) <= -1) {
            return 0;
        }
        return acos($f1 + $f2 + $f3) * $iRadius; // meters
    }

}