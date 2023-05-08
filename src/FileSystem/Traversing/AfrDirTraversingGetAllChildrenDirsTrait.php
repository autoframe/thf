<?php

namespace Autoframe\Core\FileSystem\Traversing;

use Autoframe\Core\FileSystem\DirPath\AfrDirPathTrait;
use Autoframe\Core\FileSystem\DirPath\Exception\AfrFileSystemDirPathException;
use Autoframe\Core\FileSystem\Traversing\Exception\AfrFileSystemTraversingException;

use function readdir;
use function closedir;
use function filetype;


trait AfrDirTraversingGetAllChildrenDirsTrait
{
    use AfrDirPathTrait;
    use AfrDirTraversingSortTrait;

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
    public function getAllChildrenDirs(
        string $sDirPath,
        int    $iMaxLevels = 1,
        bool   $bFollowSymlinks = false,
        string $sDirSeparator = DIRECTORY_SEPARATOR,
        int    $iCurrentLevel = 0
    )
    {

        if ($iCurrentLevel < 0) {
            $iCurrentLevel = 0;
        }
        if ($iMaxLevels <= $iCurrentLevel) {
            return false;
        }

        if ($iCurrentLevel === 0) {
            if (!$this->dirPathIsDir($sDirPath)) {
                throw new AfrFileSystemTraversingException(
                    'Invalid directory path provided in ' . __CLASS__ . '->' . __FUNCTION__ . ': "' . $sDirPath . '"'
                );
            }
            $sDirPath = $this->dirPathCorrectFormat($sDirPath, true, true, $sDirSeparator);
        }

        $aDirs = $aDirsLoop = $aDirsLoopSymlink = [];
        $rDir = $this->openDir($sDirPath);  //opendir/readdir calls are much faster than the RecursiveDirectoryIterator
        while ($sEntryName = readdir($rDir)) {
            if ($this->getDirPathIsDirAlias($sEntryName)) {
                continue;
            }
            if ($this->dirPathIsDir($sDirPath . $sEntryName)) {
                $aDirsLoop[] = $sEntryName;
            } elseif ($bFollowSymlinks && @filetype($sDirPath . $sEntryName) === 'link') {
                $sSymLinkTarget = readlink($sDirPath . $sEntryName);//TODO test symlinks
                if ($sSymLinkTarget && $this->dirPathIsDir($sSymLinkTarget)) {
                    $aDirsLoopSymlink[$sEntryName] = $sSymLinkTarget;
                }
            }
        }
        closedir($rDir);    // close directory

        if (empty($aDirsLoop)) {
            return false;
        }

        $iCurrentLevel++;
        foreach ($aDirsLoop as $sEntryName) {
            $aDirs[$sEntryName] = $this->getAllChildrenDirs(
                $sDirPath . $sEntryName . $sDirSeparator,
                $iMaxLevels,
                $bFollowSymlinks,
                $sDirSeparator,
                $iCurrentLevel
            );
        }
        if ($aDirsLoopSymlink) {
            foreach ($aDirsLoopSymlink as $sEntryName => $sSymLinkTarget) {
                $aDirs[$sEntryName] = $this->getAllChildrenDirs(
                    $sSymLinkTarget . $sDirSeparator,
                    $iMaxLevels,
                    $bFollowSymlinks,
                    $sDirSeparator,
                    $iCurrentLevel
                );
            }
        }
        $this->applyAfrDirTraversingSortMethod($aDirs, true); //ksort($aDirs);

        return $aDirs;
    }


}
