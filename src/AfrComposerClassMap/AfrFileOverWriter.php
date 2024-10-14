<?php
declare(strict_types=1);

namespace Autoframe\Core\AfrComposerClassMap;

use function file_exists;
use function file_put_contents;
use function ceil;
use function unlink;
use function usleep;
use function is_writable;
use function rename;

/**
 * It tries several times to overwrite a file, using a timeout and a maximum retry time
 */
class AfrFileOverWriter //todo: de miscat / integrat in components-filesystem
{
    /**
     * @param string $sFilePath
     * @param string $sData
     * @param int $iRetryMs
     * @param float $fDeltaSleepMs
     * @return bool
     */
    public static function overWrite(
        string $sFilePath,
        string $sData,
        int    $iRetryMs = 1500,
        float  $fDeltaSleepMs = 1.5
    ): bool
    {
        if (!file_exists($sFilePath)) {
            return file_put_contents($sFilePath, $sData) !== false;
        }
        $sFilePathAlt = $sFilePath . '.lock';
        if ($iRetryMs < 1) { //at least 1
            $iRetryMs = 1;
        }
        if ($fDeltaSleepMs < 0.01) { //at least 0.01
            $fDeltaSleepMs = 0.01;
        }

        $iDeltaUs = (int)ceil($fDeltaSleepMs * 1000);
        for ($i = 0; $i < $iRetryMs; $i += $fDeltaSleepMs) {
            if (!file_exists($sFilePathAlt)) {
                if (file_put_contents($sFilePathAlt, $sData) === false) {
                    @unlink($sFilePathAlt); //broken?
                }
                usleep($iDeltaUs);
            }
            if (file_exists($sFilePathAlt) && is_writable($sFilePathAlt) && is_writable($sFilePath)) {
                if (@rename($sFilePathAlt, $sFilePath)) {
                    return true;
                }
            }
            usleep($iDeltaUs);
        }
        return false;
    }
}