<?php

namespace Autoframe\Core\FileSystem\Versioning;

use Autoframe\Core\FileSystem\DirPath\Exception\FileSystemDirPathException;
use Autoframe\Core\FileSystem\Versioning\Exception\FileSystemVersioningException;

interface AfrDirMaxFileMtimeInterface
{
    /**
     * @param string|array $strOrArrPaths
     * @param int $iMaxSubDirs
     * @param bool $bGetTsFromDirs
     * @param bool $bFollowSymlinks
     * @return int
     * @throws FileSystemVersioningException
     * @throws FileSystemDirPathException
     */
    public function getDirMaxFileMtime($strOrArrPaths, int $iMaxSubDirs = 1, bool $bGetTsFromDirs = false, bool $bFollowSymlinks = false): int;
}