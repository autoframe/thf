<?php
declare(strict_types=1);

namespace Autoframe\Core\Export\Php;

trait AfrExportPhpArr
{
    //todo: are bugs string escape + unit test de facut
    public function exportPhpArrayAsString(
        array  $aData,
        string $sQuot = "'",
        string $sEndOfLine = '',
        string $sPointComa = ';',
        string $sVarName = '$aData'
    ): string
    {
        $sOut = '';
        foreach ($aData as $mk => $mVal) {
            $sKType = gettype($mk);
            $sVType = gettype($mVal);
            if ($sKType === 'integer' || $sKType=== 'double') {
                $sOut .= $mk;
            } elseif ($sKType === 'string') {
                $sOut .= $sQuot . addslashes($mk) . $sQuot;
            } else {
                $sOut .= $sQuot . addslashes(serialize($mk)) . $sQuot;
            }
            $sOut .= '=>';
            if ($sVType === 'integer' || $sVType=== 'double') {
                $sOut .= $mVal;
            } elseif ($sVType === 'string') {
                $sOut .= $sQuot . addslashes($mVal) . $sQuot;
            } elseif (is_array($mVal)) {
                $sOut .= $this->exportPhpArrayAsString($mVal, $sQuot, $sEndOfLine, '', '');
            }
            else {
                $sOut .= $sQuot . addslashes(serialize($mVal)) . $sQuot;
            }
            $sOut .= ',';
        }
        if ($sVarName) {
            if (substr($sVarName, 0, 1) !== '$') {
                $sVarName = '$' . $sVarName;
            }
            $sVarName .= '=';
        }
        return $sVarName . '[' . $sOut . ']' . $sPointComa . $sEndOfLine;
    }

}