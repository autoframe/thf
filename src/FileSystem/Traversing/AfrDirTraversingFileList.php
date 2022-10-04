<?php

namespace Autoframe\Core\FileSystem\Traversing;

use Autoframe\Core\FileSystem\DirPath\AfrDirPath;
use Autoframe\Core\FileSystem\Exception\FileSystemException;
use Autoframe\Core\FileSystem\Traversing\Exception\FileSystemTraversingException;
use function ltrim;
use function strtolower;
use function readdir;
use function is_file;
use function is_readable;
use function strpos;
use function substr;
use function strlen;
use function closedir;
use function is_string;
use function is_callable;
use function natsort;

trait AfrDirTraversingFileList
{
    use AfrDirPath;

    /**
     * @param string $sDirPath
     * @param array $aFilterExtensions
     * @param string $sSort
     * @return array|false
     * @throws FileSystemException
     * @throws FileSystemTraversingException
     */
    public function getDirFileList(
        string $sDirPath,
        array  $aFilterExtensions = [],
        string $sSort = 'natsort'
    )
    {
        if (!$this->dirPathIsDir($sDirPath)) {
            return false;
        }

        $sDirPath = $this->dirPathCorrectFormat($sDirPath, true, true);

        foreach ($aFilterExtensions as &$sFilter) {
            $sFilter = '.' . ltrim(strtolower($sFilter), '.');
        }

        $aFiles = array();
        $rDir = $this->openDir($sDirPath);
        if (!$rDir) {
            return false;
        }
        while ($sEntryName = readdir($rDir)) {
            $sFilePath = $sDirPath . $sEntryName;
            if (!$this->getDirPathIsDirAlias($sEntryName) && is_file($sFilePath) && is_readable($sFilePath)) {
                if (!empty($aFilterExtensions)) {
                    foreach ($aFilterExtensions as $sLowFilter) {
                        if ($sLowFilter === '.') { //files without any extension
                            if (strpos($sEntryName, '.') === false) { //file without extension
                                $aFiles[] = $sEntryName;
                                break;
                            }
                            continue; //file with extension
                        }
                        if (strtolower(substr($sEntryName, -strlen($sLowFilter))) === $sLowFilter) {
                            $aFiles[] = $sEntryName;
                            break;
                        }
                    }
                } else {
                    $aFiles[] = $sEntryName;
                }
            }
        }
        closedir($rDir);
        if (is_string($sSort)) {
            if (!is_callable($sSort)) {
                throw new FileSystemTraversingException(
                    'Invalid array sort function "' . $sSort . '" in ' . __FUNCTION__
                );
            }
            $sSort($aFiles); //natsort($aFiles);
        }
        return $aFiles;
    }


}
