<?php

namespace Autoframe\Core\FileSystem\Versioning;

use Autoframe\Core\FileSystem\DirPath\AfrDirPath;
use Autoframe\Core\FileSystem\Versioning\Exception\FileSystemVersioningException;


trait AfrDirVersioningDirMtimeHash
{
    use AfrDirPath;

    /**
     * @param string $sDirPath
     * @param bool $bCanThrowException
     * @return string
     * @throws FileSystemVersioningException
     */
    public function dirVersioningDirMtimeHash(
        string $sDirPath,
        bool   $bCanThrowException
    ): string
    {
        $sDirPath = $this->dirPathRemoveFinalSlash($sDirPath);
        $iTimestamp = (int)($bCanThrowException ? filemtime($sDirPath) : @filemtime($sDirPath));
        if (!$iTimestamp && $bCanThrowException) {
            throw new FileSystemVersioningException(
                'filemtime ' . __CLASS__ . '->' . __FUNCTION__ . 'failed in for: ' . $sDirPath
            );
        }

        return strtoupper(dechex($iTimestamp));
    }



}
