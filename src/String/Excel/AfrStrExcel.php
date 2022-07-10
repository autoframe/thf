<?php


namespace Autoframe\Core\String\Excel;

//use Autoframe\Core\Exception\Exception;

class AfrStrExcel
{
    /**
     * @param int $n
     * @return string  excel nr to 0=>A, 1=>B, 2=>C
     */
    public static function num2excel(int $n): string
    {
        if($n<0){
            $n = abs($n);
        }
        for ($r = ""; $n >= 0; $n = intval($n / 26) - 1) {
            $r = chr($n % 26 + 0x41) . $r;
        }
        return $r;
    }

}