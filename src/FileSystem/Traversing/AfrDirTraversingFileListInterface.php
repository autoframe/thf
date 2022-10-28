<?php

namespace Autoframe\Core\FileSystem\Traversing;

use Autoframe\Core\FileSystem\Exception\FileSystemException;
use Autoframe\Core\FileSystem\Traversing\Exception\FileSystemTraversingException;

interface AfrDirTraversingFileListInterface
{
    /**
     * @param string $sDirPath
     * @param array $aFilterExtensions
     * @return array|false
     * @throws FileSystemException
     * @throws FileSystemTraversingException
     */
    public function getDirFileList(string $sDirPath, array $aFilterExtensions = []);
}