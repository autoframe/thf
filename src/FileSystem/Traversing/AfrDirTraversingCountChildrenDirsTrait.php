<?php
declare(strict_types=1);

namespace Autoframe\Core\FileSystem\Traversing;

use Autoframe\Core\FileSystem\Exception\AfrFileSystemException;

use function is_array;
use function count;

trait AfrDirTraversingCountChildrenDirsTrait
{
    use AfrDirTraversingGetAllChildrenDirsTrait;

    /**
     * @param string $sDirPath
     * @param bool $bCountSymlinksAsDirs
     * @return false|int
     * @throws Exception\AfrFileSystemTraversingException
     * @throws AfrFileSystemException
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
