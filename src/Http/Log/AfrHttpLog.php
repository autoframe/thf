<?php

namespace Autoframe\Core\Http\Log;

use Autoframe\Core\Http\Request\AfrHttpRequest;

trait AfrHttpLog
{
    use AfrHttpRequest;

    /** Over-writable in order to custom log redirects
     * @return void
     */
    protected function hXxxLog()
    {
        //TODO de testat!
        print_r($this->getMinifiedBacktrace(1));
        //AfrError::getMinifiedBacktrace;
    }

    /**
     * AfrError::getMinifiedBacktrace;
     * @param int $iRemoveLastNLevels
     * @return array
     */
    protected function getMinifiedBacktrace(int $iRemoveLastNLevels = 1) {
        $aHuge = debug_backtrace();
        $aHuge = array_slice($aHuge, $iRemoveLastNLevels);
        $aStack = [];
        foreach ( $aHuge as $iKey => & $aItem ) {
            if( isset( $aItem['object'] ) ) {
                unset( $aHuge[ $iKey ][ 'object' ] );
            }
            if( isset( $aItem['args'] ) ) {
                unset( $aHuge[ $iKey ][ 'args' ] );
            }

            $aStack[] =
                ($aItem['file'] ?? '---'). ':' .
                ($aItem['line'] ?? '---'). ' > ' .
                ($aItem['class'] ?? '---'). '::' .
                ($aItem['function'] ?? '---'). ' - ' .
                ($aItem['line'] ?? '---');

        }
        unset($aItem);
        return $aStack;
    }
    /**
     * @param string $dir
     * @param string $sExtension
     * @param bool $bSerialize
     * @return array
     */
    protected function logHttpRequestedToFile(string $dir = '.', string $sExtension = 'txt', bool $bSerialize = false): array
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