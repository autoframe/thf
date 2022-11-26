<?php

namespace Autoframe\Core\FileSystem\Traversing;

use Autoframe\Core\FileSystem\DirPath\AfrDirPath;
use Autoframe\Core\FileSystem\Exception\AfrFileSystemException;
use Autoframe\Core\FileSystem\Traversing\Exception\AfrFileSystemTraversingException;
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
    use AfrDirTraversingSort;

    /**
     * @param string $sDirPath
     * @param array $aFilterExtensions
     * @return array|false
     * @throws AfrFileSystemException
     * @throws AfrFileSystemTraversingException
     */
    public function getDirFileList(
        string $sDirPath,
        array  $aFilterExtensions = []
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

        $this->applyAfrDirTraversingSortMethod($aFiles, false);

        return $aFiles;
    }


}
