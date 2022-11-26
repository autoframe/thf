<?php

namespace Autoframe\Core\Http\Log;

use Autoframe\Core\Http\Request\AfrHttpRequest;

trait AfrHttpLog
{
    use AfrHttpRequest;

    /**
     * @param string $dir
     * @param string $sExtension
     * @param bool $bSerialize
     * @return array
     */
    protected function logHttpRequested(string $dir = '.', string $sExtension = 'txt', bool $bSerialize = false): array
    {
        if (!$dir) {
            $dir = __DIR__;
        }
        if (!$dir) {
            $dir = '.'.DIRECTORY_SEPARATOR;
        }
        $aOut = $this->getHttpRequested(true,true,true,true,true,true);

        $sFilename = date('Y-m-d_H-i-s_') .
            microtime() . '_' .
            $_SERVER['REQUEST_METHOD'] . '_' .
            $_SERVER['REQUEST_URI'] . '.' . $sExtension;
        $sFilename = str_replace(str_split('<>\/|:*?" ', 1), '_', $sFilename);
        $sFilename = rawurldecode($sFilename);
        $sPath = rtrim($dir, ' /\\') . DIRECTORY_SEPARATOR . $sFilename;

        file_put_contents($sPath, $bSerialize ? serialize($aOut) : print_r($aOut, true), FILE_APPEND);

        return $aOut;
    }

}