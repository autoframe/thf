<?php

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
            $sKtype = gettype($mk);
            $sVType = gettype($mVal);
            if ($sKtype === 'integer' || $sKtype=== 'float') {
                $sOut .= $mk;
            } elseif ($sKtype === 'string') {
                $sOut .= $sQuot . addslashes($mk) . $sQuot;
            } else {
                $sOut .= $sQuot . addslashes(serialize($mk)) . $sQuot;
            }
            $sOut .= '=>';
            if ($sVType === 'integer' || $sVType=== 'float') {
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