<?php
declare(strict_types=1);

namespace Autoframe\Core\FileSystem\Versioning;

use Autoframe\Core\FileSystem\Versioning\Exception\AfrFileSystemVersioningException;

use function rtrim;
use function filemtime;
use function strtoupper;
use function dechex;

trait AfrFileVersioningMtimeHashTrait
{
    /**
     * @param string $sDirPath
     * @param bool $bCanThrowException
     * @return string
     * @throws AfrFileSystemVersioningException
     */
    public function fileVersioningMtimeHash(
        string $sDirPath,
        bool   $bCanThrowException
    ): string
    {
        $sDirPath = rtrim($sDirPath, '\/');
        $iTimestamp = (int)($bCanThrowException ? filemtime($sDirPath) : @filemtime($sDirPath));
        if (!$iTimestamp && $bCanThrowException) {
            throw new AfrFileSystemVersioningException(
                'filemtime ' . __CLASS__ . '->' . __FUNCTION__ . 'failed in for: ' . $sDirPath
            );
        }

        return strtoupper(dechex($iTimestamp));
    }
}
