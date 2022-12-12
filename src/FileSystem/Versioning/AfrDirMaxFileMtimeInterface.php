<?php

namespace Autoframe\Core\FileSystem\Versioning;

use Autoframe\Core\FileSystem\DirPath\Exception\AfrFileSystemDirPathException;
use Autoframe\Core\FileSystem\Versioning\Exception\AfrFileSystemVersioningException;

interface AfrDirMaxFileMtimeInterface
{
    /**
     * @param string|array $strOrArrPaths
     * @param int $iMaxSubDirs
     * @param bool $bGetTsFromDirs
     * @param bool $bFollowSymlinks
     * @return int
     * @throws AfrFileSystemVersioningException
     * @throws AfrFileSystemDirPathException
     */
    public function getDirMaxFileMtime($strOrArrPaths, int $iMaxSubDirs = 1, bool $bGetTsFromDirs = false, bool $bFollowSymlinks = false): int;
}