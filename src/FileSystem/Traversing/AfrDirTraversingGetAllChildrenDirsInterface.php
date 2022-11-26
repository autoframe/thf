<?php

namespace Autoframe\Core\FileSystem\Traversing;

use Autoframe\Core\FileSystem\DirPath\Exception\AfrFileSystemDirPathException;
use Autoframe\Core\FileSystem\Traversing\Exception\AfrFileSystemTraversingException;

interface AfrDirTraversingGetAllChildrenDirsInterface
{
    /**
     * @param string $sDirPath
     * @param int $iMaxLevels
     * @param bool $bFollowSymlinks
     * @param string $sDirSeparator
     * @param int $iCurrentLevel
     * @return array|false
     * @throws AfrFileSystemTraversingException
     * @throws AfrFileSystemDirPathException
     */
    public function getAllChildrenDirs(string $sDirPath, int $iMaxLevels = 1, bool $bFollowSymlinks = false, string $sDirSeparator = DIRECTORY_SEPARATOR, int $iCurrentLevel = 0);
}