<?php

namespace Autoframe\Core\FileSystem\Traversing;

use Autoframe\Core\FileSystem\Exception\FileSystemException;

use function is_array;
use function count;

trait AfrDirTraversingCountChildrenDirs
{
    use AfrDirTraversingGetAllChildrenDirs;

    /**
     * @param string $sDirPath
     * @param bool $bCountSymlinksAsDirs
     * @return false|int
     * @throws Exception\FileSystemTraversingException
     * @throws FileSystemException
     */
    public function dirPathCountChildrenDirs(string $sDirPath, bool $bCountSymlinksAsDirs = false)
    {
        $mChildrenDirs = $this->getAllChildrenDirs(
            $sDirPath,
            1,
            $bCountSymlinksAsDirs
        );
        return is_array($mChildrenDirs) ? count($mChildrenDirs) : $mChildrenDirs;
    }

}
