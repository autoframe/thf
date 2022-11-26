<?php

namespace Autoframe\Core\FileSystem\Traversing;

use Autoframe\Core\FileSystem\Exception\AfrFileSystemException;
use Autoframe\Core\FileSystem\Traversing\Exception\AfrFileSystemTraversingException;

interface AfrDirTraversingFileListInterface
{
    /**
     * @param string $sDirPath
     * @param array $aFilterExtensions
     * @return array|false
     * @throws AfrFileSystemException
     * @throws AfrFileSystemTraversingException
     */
    public function getDirFileList(string $sDirPath, array $aFilterExtensions = []);
}