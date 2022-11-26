<?php

namespace Autoframe\Core\FileSystem\Traversing;

use Autoframe\Core\FileSystem\Exception\AfrFileSystemException;

interface AfrDirTraversingCountChildrenDirsInterface
{
    /**
     * @param string $sDirPath
     * @param bool $bCountSymlinksAsDirs
     * @return false|int
     * @throws Exception\AfrFileSystemTraversingException
     * @throws AfrFileSystemException
     */
    public function dirPathCountChildrenDirs(string $sDirPath, bool $bCountSymlinksAsDirs = false);
}