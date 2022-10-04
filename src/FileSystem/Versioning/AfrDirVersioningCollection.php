<?php

namespace Autoframe\Core\FileSystem\Versioning;

use Autoframe\Core\FileSystem\DirPath\AfrDirPath;
use Autoframe\Core\FileSystem\Versioning\Exception\FileSystemVersioningException;
use Autoframe\Core\FileSystem\Versioning\AfrDirVersioningDirMtimeHash;
use Autoframe\Core\FileSystem\Versioning\AfrDirMaxFileMtime;

trait AfrDirVersioningCollection
{
    use AfrDirPath;
    use AfrDirVersioningDirMtimeHash;
    use AfrDirMaxFileMtime;
/*
    public function dir_vers($mDirPath, bool $bReturnTimeStampInt = false, int $iMaxSubdirs = 1)
    {
        $iTs = 0;
        if (is_array($mDirPath) && count($mDirPath)) {//array of file and dir paths
            foreach ($mDirPath as $i => $mPath) {
                $iTs = max($iTs, dir_vers($mPath, true, $iMaxSubdirs));
            }
        } elseif (is_file($mDirPath) && is_readable($mDirPath)) {
            $iTs = filemtime($mDirPath);
        } elseif ($this->dirPathIsDir($mDirPath)) {
            $myDirectory = $this->openDir($mDirPath);
            while ($entry = readdir($myDirectory)) {
                if ($this->getDirPathIsDirAlias($entry)) {
                    continue;
                }
                $tf = $this->dirPathRemoveFinalSlash($mDirPath) . DIRECTORY_SEPARATOR . $entry;
                $sType = filetype($tf);
                if ($sType === 'file') {
                    $iTs = max($iTs, (int)filemtime($tf));
                } elseif ($sType === 'dir' && $iMaxSubdirs > 0) {
                    $iTs = max($iTs, dir_vers($tf, true, ($iMaxSubdirs - 1)));
                }
            }
            closedir($myDirectory);
        }

        return $bReturnTimeStampInt ? $iTs : strtoupper(dechex($iTs));

    }
*/
    public function dir_hash($sDirPath, $bMd5Contents = false, $iMaxSubdirs = 1): string
    {
        $v = '';
        if (is_array($sDirPath)) {//array of file and dir paths
            foreach ($sDirPath as $i => $path) {
                $v .= $this->dir_hash($path, $bMd5Contents, $iMaxSubdirs);
            }
        } elseif (is_file($sDirPath) && is_readable($sDirPath)) {
            if ($bMd5Contents == 1) {
                $v = md5(file_get_contents($sDirPath));
            } else {
                $v = filesize($sDirPath) . filemtime($sDirPath) . $sDirPath;
            }
        } elseif ($this->dirPathIsDir($sDirPath)) {
            $myDirectory = $this->openDir($sDirPath);
            while ($entry = readdir($myDirectory)) {
                if ($this->getDirPathIsDirAlias($entry)) {
                    continue;
                }
                $tf = $this->dirPathRemoveFinalSlash($sDirPath) . DIRECTORY_SEPARATOR . $entry;


                $sType = filetype($tf);
                if ($sType === 'file') {
                    $v .= $this->dir_hash($tf, $bMd5Contents);
                } elseif ($iMaxSubdirs > 0 && $sType === 'dir') {
                    $v .= $this->dir_hash($tf, $bMd5Contents, $iMaxSubdirs - 1);
                }
            }
            closedir($myDirectory);
        }

        if ($v) {
            return md5($v);
        }
        return '';
    }


}
