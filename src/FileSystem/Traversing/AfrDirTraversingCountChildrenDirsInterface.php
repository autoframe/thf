<?php

namespace Autoframe\Core\FileSystem\Traversing;

use Autoframe\Core\FileSystem\Exception\FileSystemException;

interface AfrDirTraversingCountChildrenDirsInterface
{
    /**
     * @param string $sDirPath
     * @param bool $bCountSymlinksAsDirs
     * @return false|int
     * @throws Exception\FileSystemTraversingException
     * @throws FileSystemException
     */
    public function dirPathCountChildrenDirs(string $sDirPath, bool $bCountSymlinksAsDirs = false);
}