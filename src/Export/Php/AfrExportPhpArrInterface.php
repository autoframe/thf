<?php

namespace Autoframe\Core\Export\Php;

interface AfrExportPhpArrInterface
{
    public function exportPhpArrayAsString(array $aData, string $sQuot = "'", string $sEndOfLine = '', string $sPointComa = ';', string $sVarName = '$aData'): string;
}