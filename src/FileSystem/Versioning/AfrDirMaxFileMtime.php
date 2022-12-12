<?php

namespace Autoframe\Core\FileSystem\Versioning;

use Autoframe\Core\FileSystem\DirPath\AfrDirPath;
use Autoframe\Core\FileSystem\DirPath\Exception\AfrFileSystemDirPathException;
use Autoframe\Core\FileSystem\Versioning\Exception\AfrFileSystemVersioningException;

use function is_array;
use function is_string;
use function strlen;
use function max;
use function filemtime;
use function filetype;
use function readdir;
use function readlink;
use function closedir;
use function gettype;

trait AfrDirMaxFileMtime
{
    use AfrDirPath;

    /**
     * @param string|array $strOrArrPaths
     * @param int $iMaxSubDirs
     * @param bool $bGetTsFromDirs
     * @param bool $bFollowSymlinks
     * @return int
     * @throws AfrFileSystemVersioningException
     * @throws AfrFileSystemDirPathException
     */
    public function getDirMaxFileMtime(
        $strOrArrPaths,
        int $iMaxSubDirs = 1,
        bool $bGetTsFromDirs = false,
        bool $bFollowSymlinks = false
    ): int
    {
        if ($iMaxSubDirs < 0) {
            return 0;
        }
        $iMaxTimestamp = 0;

        if (is_array($strOrArrPaths)) {
            foreach ($strOrArrPaths as $sDirPath) {
                $iMaxTimestamp = max(
                    $iMaxTimestamp,
                    $this->getDirMaxFileMtime($sDirPath, $iMaxSubDirs, $bGetTsFromDirs, $bFollowSymlinks)
                );
            }
        } elseif (is_string($strOrArrPaths)) {
            if (strlen($strOrArrPaths) < 1 || $this->getDirPathIsDirAlias($strOrArrPaths)) {
                throw new AfrFileSystemVersioningException(
                    'Invalid string "' . $strOrArrPaths . '" provided for ' .
                    __CLASS__ . '->' . __FUNCTION__
                );
            }
            $sPath = $strOrArrPaths;
            $aDirs = [];

            $sPathType = $this->getDirMaxFileMtimeProcess(
                $sPath,
                $iMaxTimestamp,
                $aDirs,
                $bFollowSymlinks,
                $bGetTsFromDirs
            );

            if ($sPathType === 'dir') {
                $sPath = $this->dirPathCorrectFormat($sPath);
                $rDir = $this->openDir($sPath);
                while ($sEntryName = readdir($rDir)) {
                    if ($this->getDirPathIsDirAlias($sEntryName)) {
                        continue;
                    }
                    $this->getDirMaxFileMtimeProcess(
                        $sPath . $sEntryName,
                        $iMaxTimestamp,
                        $aDirs,
                        $bFollowSymlinks,
                        $bGetTsFromDirs
                    );
                }
                closedir($rDir);
                if($bGetTsFromDirs){
                    $iMaxTimestamp = max(
                        $iMaxTimestamp,
                        filemtime($sPath)
                    );
                    echo "~~ $sPath ~~ \n\n";
                }
            }
            foreach ($aDirs as $tf) {
                $iMaxTimestamp = max(
                    $iMaxTimestamp,
                    $this->getDirMaxFileMtime($tf, $iMaxSubDirs - 1, $bGetTsFromDirs)
                );
            }

        } else {
            throw new AfrFileSystemVersioningException(
                'Expected string|array as parameter 1 but yoy have provided"' .
                gettype($strOrArrPaths) . '" in ' .
                __CLASS__ . '->' . __FUNCTION__
            );
        }

        return $iMaxTimestamp;
    }

    private function getDirMaxFileMtimeProcess(
        string $tf,
        int    &$iMaxTimestamp,
        array  &$aDirs,
        bool   $bFollowSymlinks,
        bool   $bGetTsFromDirs
    ): string
    {
        $sType = (string)filetype($tf);
        if (!$sType) {
            return '';
        }
        if ($sType === 'file') {
            $iMaxTimestamp = max($iMaxTimestamp, (int)filemtime($tf));
        } elseif ($sType === 'dir') {
            $aDirs[] = $tf; //keep low the open dir resource count
            if($bGetTsFromDirs){
                $iMaxTimestamp = max(
                    $iMaxTimestamp,
                    filemtime($tf)
                );
            }
        } elseif ($bFollowSymlinks && $sType === 'link') {
            $sSymLinkTarget = readlink($tf);//TODO test symlinks
            if ($sSymLinkTarget) {
                $sType = $this->getDirMaxFileMtimeProcess(
                    $sSymLinkTarget,
                    $iMaxTimestamp,
                    $aDirs,
                    true,
                    $bGetTsFromDirs
                );
            }
        }
        return $sType;
    }

}
