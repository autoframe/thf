<?

namespace Autoframe\Core\BackupBPG;
if (!defined('__DIR__')) {
    define('__DIR__', dirname(__FILE__));
}


class PhpBackupClass
{
    var $sourceFolder;
    var $destinationFolder;
    var $sLatestFolderName = '!latest';
    var $aActionLog = array();
    var $today = null;

    var $datePattern = 'Ymd';

    var $aFiletype = array();
    var $sDirPermissions = '0775';
    var $sDs = DIRECTORY_SEPARATOR;
    var $tmp = array();

    /**
     * @param $sSourcePath
     * @param $sDestinationPath
     * @param $dToday
     */
    function PhpBackupClass($sSourcePath, $sDestinationPath, $dToday = null)
    {
        if ($dToday) {
            $this->today = $dToday;
        } elseif (!$this->today) {
            $this->today = date($this->datePattern);
        }
        $this->sourceFolder = $this->fixSlashStyle($sSourcePath, true);
        $this->destinationFolder = $this->fixSlashStyle($sDestinationPath, true);
    }

    /**
     * @param string $sDirPath
     * @param bool $bForceToDs
     * @return string
     */
    function fixSlashStyle($sDirPath, $bForceToDs = false)
    {
        if ($bForceToDs) {
            $sW = '\\';
            $sL = '/';
            $aMap = array(
                $sW => $sL,
                $sL => $sW
            );
            $sDirPath = str_replace($aMap[$this->sDs], $this->sDs, $sDirPath);
        }
        return rtrim($sDirPath, '\/');
    }


    /**
     * @return string
     */
    function getDestinationFolderWithFolderName()
    {
        return $this->fixSlashStyle($this->destinationFolder) . $this->sDs . $this->sLatestFolderName;
    }

    /**
     * @param string $sToday
     * @return string
     */
    function getDestinationDayBackupFolderPath()
    {
        return $this->fixSlashStyle($this->destinationFolder) . $this->sDs . $this->today;
    }

    /**
     * @param string $dir
     * @return bool
     */
    function isDirEmpty($dir)
    {
        if (!$this->isDir($dir)) {
            return false;
        }
        $handle = opendir($dir);
        while (false !== ($entry = readdir($handle))) {
            if ($entry != '.' && $entry != '..') {
                closedir($handle);
                return false;
            }
        }
        closedir($handle);
        return true;
    }

    /**
     * @param string $sDirPath
     * @return bool
     */
    function rmdir($sDirPath)
    {
        $sDirPath = $this->fixSlashStyle($sDirPath);
        if ($this->isDirEmpty($sDirPath)) {
            $bResponse = rmdir($sDirPath);
            if ($bResponse) {
                //$this->aActionLog['deleted'][] = 'Removed Empty Folder ' . $sDirPath;
                $this->ActionLog('deleted', 'Removed Empty Folder $s', $sDirPath, '');
                unset($this->aFiletype[$sDirPath]);
            } else {
                //$this->aActionLog['err'][] = 'ERROR Folder not removable ' . $sDirPath;
                $this->ActionLog('err', 'ERROR Folder not removable $s', $sDirPath, '');
            }
            return $bResponse;
        }
        return false;
    }

    /**
     * @param string $sFilePath
     * @return bool
     */
    function unlink($sFilePath)
    {
        if ($this->isFile($sFilePath)) {
            $bResponse = unlink($sFilePath);
            if ($bResponse) {
                //$this->aActionLog['deleted'][] = 'Removed File ' . $sFilePath;
                $this->ActionLog('deleted', 'Removed File $s', $sFilePath, '');

                unset($this->aFiletype[$sFilePath]);
            } else {
                //$this->aActionLog['err'][] = 'ERROR File not removable ' . $sFilePath;
                $this->ActionLog('err', 'ERROR File not removable $s', $sFilePath, '');
            }
            return $bResponse;
        }
        return false;
    }


    /**
     * @param string $sCopyToPath
     * @param bool $bMakeDir
     * @return string
     */
    function getDestinationDateBackupFolderPathFromDestinationPath($sCopyToPath, $bMakeDir = true)
    {
        if (empty($this->tmp[__FUNCTION__])) {
            $this->tmp[__FUNCTION__] = array(
                $this->getDestinationDayBackupFolderPath(),
                strlen($this->getDestinationFolderWithFolderName())
            );
        }
        $sCopyToDateFilePath = $this->tmp[__FUNCTION__][0] . substr($sCopyToPath, $this->tmp[__FUNCTION__][1]);
        if ($bMakeDir) {
            $sBackupDateFolderPath = substr($sCopyToDateFilePath, 0, -strlen(basename($sCopyToPath)) - 1);
            if (!$this->isDir($sBackupDateFolderPath)) {
                $this->mkdir($sBackupDateFolderPath);
            }
        }


        return $sCopyToDateFilePath;


    }


    /**
     * @param string $sDirPath
     * @return array
     */
    function getDirFileList($sDirPath)
    {
        $aContents = array();
        $handle = opendir($sDirPath);
        if ($handle) {
            while (false !== ($entry = readdir($handle))) {
                if ($entry != '.' && $entry != '..' && $this->fileExists($sDirPath . $this->sDs . $entry)) {
                    $aContents[] = $entry;
                }
            }
            closedir($handle);
        }
        return $aContents;
    }

    /**
     * @param $sPath
     * @param bool $bForce
     * @return false|mixed|string
     */
    function filetype($sPath, $bForce = false)
    {
        $sPath = $this->fixSlashStyle($sPath);
        if (!isset($this->aFiletype[$sPath]) || $bForce) {
            //Possible values are fifo, char, dir, block, link, file, socket and unknown.
            $this->aFiletype[$sPath] = @filetype($sPath);
        }
        return $this->aFiletype[$sPath];
    }

    /**
     * @param string $sDirPath
     * @return bool
     */
    function isDir($sDirPath)
    {
        return $this->filetype($sDirPath) === 'dir';
    }

    /**
     * @param string $sPath
     * @return bool
     */
    function isFile($sPath)
    {
        return $this->filetype($sPath) === 'file';
    }

    /**
     * File or Directory
     * @param string $sPath
     * @return bool
     */
    function fileExists($sPath)
    {
        return $this->isFile($sPath) || $this->isDir($sPath);
    }

    /**
     * @param string $sDestinationDir
     * @return bool
     */
    function mkdir($sDestinationDir)
    {
        if ($this->isDir($sDestinationDir)) {
            return true;
        }
        $bStatus = mkdir($sDestinationDir, $this->sDirPermissions);
        if (!$bStatus) { //try to emulate something recursive
            $aDestinationDir = explode($this->sDs, $sDestinationDir);
            unset($aDestinationDir[count($aDestinationDir) - 1]); //pop last part
            $sNewDestinationDir = rtrim(implode($this->sDs, $aDestinationDir), '\/');
            if ($sNewDestinationDir == $sDestinationDir || $sNewDestinationDir == '.') {
                return false;
            }
            if (strlen($sNewDestinationDir) <= strlen($this->destinationFolder) || strlen($sNewDestinationDir) < 5) {
                return $bStatus;
            }
            $this->mkdir($sNewDestinationDir);
            $bStatus = mkdir($sDestinationDir, $this->sDirPermissions);
        }

        if ($bStatus) {
            $this->aFiletype[$sDestinationDir] = 'dir';
            //$this->aActionLog['add'][] = 'Folder Created ' . $sDestinationDir;
            $this->ActionLog('add', 'Folder Created $s', $sDestinationDir, '');
        } else {
            //$this->aActionLog['err'][] = 'ERROR Folder not created ' . $sDestinationDir;
            $this->ActionLog('err', 'ERROR Folder not created $s', $sDestinationDir, '');
        }
        return $bStatus;
    }

    function makeBackup()
    {
        echo "\n++++++++++ START @ " . date('Y-m-d H:i:s') . " ++++++++++++++++\n\n";

        if (!$this->isDir($this->sourceFolder)) {
            die('Source Folder not found. Please check your Input: ' . $this->sourceFolder);
        }
        $bSourceDestination = $this->recursiveSourceDestinationCopy(
            $this->sourceFolder,
            $this->getDestinationFolderWithFolderName()
        );

        if ($bSourceDestination) {
            $bDeletedSave = $this->recursiveFoundInDestinationAndDeletedFromSource(
                $this->sourceFolder,
                $this->getDestinationFolderWithFolderName()
            );
        }
        if ($bSourceDestination && $bDeletedSave) {
            echo "SUCCESS RUN\n";
        } else {
            echo "ERROR RUN\n";
        }
        if (count($this->aActionLog) < 1) {
            $this->ActionLog('confirm', 'All files and folder already updated', '', '');
        }
        echo $this->write_log();

    }

    /**
     * @param $sSourceDir
     * @param $sDestinationDir
     * @return bool
     */
    function recursiveSourceDestinationCopy($sSourceDir, $sDestinationDir)
    {
        $sDestinationDir = $this->fixSlashStyle($sDestinationDir);
        //first check destination folder exist or not
        if (!$this->isDir($sDestinationDir)) {
            if (!$this->mkdir($sDestinationDir)) {
                return false;
            }
        }
        $sSourceDir = $this->fixSlashStyle($sSourceDir);
        foreach ($this->getDirFileList($sSourceDir) as $sListItemName) {
            $sCopyFromPath = $sSourceDir . $this->sDs . $sListItemName;
            $sCopyToPath = $sDestinationDir . $this->sDs . $sListItemName;
            $mType = $this->filetype($sCopyFromPath);

            if ($mType === 'dir') {
                $this->recursiveSourceDestinationCopy($sCopyFromPath, $sCopyToPath);
            } elseif ($mType === 'file') {
                $this->safeCopyFilesFromSourceToDestination($sCopyFromPath, $sCopyToPath);
            } else {
                //$this->aActionLog['skip'][] = 'Skipped because type `' . $mType . '` ' . $sCopyFromPath;
                $this->ActionLog('skip', 'Skipped $s because type `$d`', $sCopyFromPath, $mType);
            }
        }

        return true;
    }

    /**
     * @param $sSourceDir
     * @param $sDestinationDir
     * @return bool
     */
    function recursiveFoundInDestinationAndDeletedFromSource($sSourceDir, $sDestinationDir)
    {
        $sDestinationDir = $this->fixSlashStyle($sDestinationDir);
        $sSourceDir = $this->fixSlashStyle($sSourceDir);
        //first check destination folder exist or not
        if (!$this->isDir($sDestinationDir)) {
            return false;
        }
        foreach ($this->getDirFileList($sDestinationDir) as $sDestinationListItemName) {
            $sSourcePath = $sSourceDir . $this->sDs . $sDestinationListItemName;
            $sDestinationPath = $sDestinationDir . $this->sDs . $sDestinationListItemName;
            $mType = $this->filetype($sDestinationPath);

            if ($mType === 'dir') {
                if (!$this->isDir($sSourcePath)) {
                    $this->recursiveFoundInDestinationAndDeletedFromSource($sSourcePath, $sDestinationPath);
                    $this->rmdir($sDestinationPath);
                }

            } elseif ($mType === 'file') {
                if (!$this->isFile($sSourcePath)) {
                    $this->safeKeepFilesDeletedFromSourceToDestinationDay($sSourcePath, $sDestinationPath);
                }
            } else {
                //$this->aActionLog['skip'][] = 'Skipped because type `' . $mType . '` ' . $sSourcePath;
                $this->ActionLog('skip', 'Skipped $s because type `$d`', $sSourcePath, $mType);

            }
        }
        return true;
    }


    function arrayFlatten($array, $return = array())
    {
        for ($x = 0; $x <= count($array); $x++) {
            if (isset($array[$x]) && is_array($array[$x])) {
                $return = $this->arrayFlatten($array[$x], $return);
            } elseif (isset($array[$x])) {
                $return[] = $array[$x];
            }
        }
        return $return;
    }


    /**
     * @param $sFrom
     * @param $sTo
     * @return bool
     */
    function copy($sFrom, $sTo)
    {
        if (!$this->isFile($sFrom)) {
            //$this->aActionLog['err'][] = 'FILE TYPE ERROR `' . $this->aFiletype[$sFrom] . '` from ' . $sFrom;
            $this->ActionLog('err', 'FILE TYPE ERROR `$d` $s', $sFrom, $this->aFiletype[$sFrom]);

            return false;
        }
        $bOverwrite = $this->isFile($sTo);
        $bR = copy($sFrom, $sTo);
        if ($bR) {
            if ($bOverwrite) {
                //$this->aActionLog['overwrite'][] = 'File overwrite from ' . $sFrom . ' to ' . $sTo;
                $this->ActionLog('overwrite', 'File overwrite from $s to $d', $sFrom, $sTo);
            } else {
                //$this->aActionLog['add'][] = 'File copied from ' . $sFrom . ' to ' . $sTo;
                $this->ActionLog('add', 'File copied from $s to $d', $sFrom, $sTo);
            }
            $this->aFiletype[$sTo] = 'file';
        } else {
            //$this->aActionLog['err'][] = 'ERROR when copy file from ' . $sFrom . ' to ' . $sTo;
            $this->ActionLog('err', 'ERROR when copy file from $s to $d', $sFrom, $sTo);
            $this->filetype($sTo, true);
        }
        return $bR;
    }

    /**
     * @param $sFrom
     * @param $sTo
     * @param bool $bKeepAllCopies
     * @param bool $bDeleteAction
     * @return bool
     */
    function move($sFrom, $sTo, $bKeepAllCopies = false, $bDeleteAction = false)
    {
        if (!$this->isFile($sFrom)) {
            //$this->aActionLog['err'][] = 'FILE TYPE ERROR `' . $this->aFiletype[$sFrom] . '` from ' . $sFrom;
            $this->ActionLog('err', 'FILE TYPE ERROR `$d` from $s', $sFrom, $this->aFiletype[$sFrom]);

            return false;
        }
        $sMsgDel = $bDeleteAction ? 'Deleted From Source: ' : '';
        $bOverwrite = $this->isFile($sTo);
        if ($bOverwrite && $bKeepAllCopies) {
            $bRx = rename($sTo, $sTo . '.V' . date('Hi'));
        }
        $bR = rename($sFrom, $sTo);
        if ($bR) {
            if ($bOverwrite) {
                if ($bKeepAllCopies) {
                    //$this->aActionLog['add'][] = $sMsgDel . 'Copy overwrite prevented ' . $sFrom . ' to ' . $sTo;
                    $this->ActionLog('add', $sMsgDel . 'Copy overwrite prevented $s to $d', $sFrom, $sTo);

                } else {
                    //$this->aActionLog['overwrite'][] = $sMsgDel . 'Move overwrite from ' . $sFrom . ' to ' . $sTo;
                    $this->ActionLog('overwrite', $sMsgDel . 'Move overwrite from $s to $d', $sFrom, $sTo);

                }

            } else {
                //$this->aActionLog['add'][] = $sMsgDel . 'File moved from ' . $sFrom . ' to ' . $sTo;
                $this->ActionLog('add', $sMsgDel . 'File moved from $s to $d', $sFrom, $sTo);

            }
            unset($this->aFiletype[$sFrom]);
            $this->aFiletype[$sTo] = 'file';
        } else {
            //$this->aActionLog['err'][] = $sMsgDel . 'ERROR when moving file from ' . $sFrom . ' to ' . $sTo;
            $this->ActionLog('err', $sMsgDel . 'ERROR when moving file from $s to $d', $sFrom, $sTo);

            $this->filetype($sFrom, true);
            $this->filetype($sTo, true);
        }
        return $bR;
    }


    /**
     * @param string $sCopyFromPath
     * @param string $sCopyToPath
     * @return void
     */
    function safeCopyFilesFromSourceToDestination($sCopyFromPath, $sCopyToPath)
    {
        if ($this->isFile($sCopyToPath)) {
            $iSourceFileSize = (int)filesize($sCopyFromPath);
            $iDestinationFileSize = (int)filesize($sCopyToPath);

            if ($iSourceFileSize != $iDestinationFileSize) {

                $sCopyToDateFilePath = $this->getDestinationDateBackupFolderPathFromDestinationPath($sCopyToPath);

                /*    $sBackupDateFolderPath = substr($sCopyToDateFilePath, 0, -strlen(basename($sCopyToPath)) - 1);
                    if (!$this->isDir($sBackupDateFolderPath)) {
                        $this->mkdir($sBackupDateFolderPath);
                    }*/

                //move destination file to date folder and keep first version from today near 00:01 AM
                if (!$this->isFile($sCopyToDateFilePath)) {
                    $this->move($sCopyToPath, $sCopyToDateFilePath, true);
                }
                //once file moved to date folder
                // now copy original file to destination folder
                $this->copy($sCopyFromPath, $sCopyToPath);

            }
        } else {
            $this->copy($sCopyFromPath, $sCopyToPath);
        }
    }

    /**
     * @param string $sSourcePath
     * @param string $sDestinationPath
     * @return void
     */
    function safeKeepFilesDeletedFromSourceToDestinationDay($sSourcePath, $sDestinationPath)
    {
        $sCopyToDateFilePath = $this->getDestinationDateBackupFolderPathFromDestinationPath($sDestinationPath);

        //move destination file to date folder and keep first version from today near 00:01 AM
        $this->move($sDestinationPath, $sCopyToDateFilePath, true, true);

    }

    function json_encode($arrayus)
    {
        $newarr = '{';
        foreach ($arrayus as $key => $val) {
            $newarr .= '"' . str_replace('"', '`', $key) . '":"' . str_replace('"', '`', $val) . '",';
        }
        $newarr = substr($newarr, 0, strlen($newarr) - 1);
        $newarr .= '}';
        return $newarr;
    }

    function ActionLog($sLogType, $sMsg, $s, $d)
    {
        $this->aActionLog[strtoupper($sLogType)][] = array(
            'msg' => $sMsg,
            's' => $s,
            'd' => $d
        );
    }

    function write_log()
    {

        $sLogTypeHeader = '';
        $source = $this->sourceFolder . $this->sDs;
        $dest = $this->destinationFolder . $this->sDs;
        $sOut = '$s=' . $source . PHP_EOL . '$d=' . $dest . PHP_EOL;
        foreach ($this->aActionLog as $sLogType => $aRows) {
            foreach ($aRows as $aData) {
                if ($sLogTypeHeader != $sLogType) {
                    $sOut .= '~~~~~~~~~~~~~~~~~~' . PHP_EOL;
                    $sOut .= $sLogType . PHP_EOL . PHP_EOL;
                    $sLogTypeHeader = $sLogType;
                }
                if ($aData['s']) {
                    $aData['s'] = str_replace($source, '', $aData['s']);
                    $aData['s'] = str_replace($dest, '', $aData['s']);
                }
                if ($aData['d']) {
                    $aData['d'] = str_replace($source, '', $aData['d']);
                    $aData['d'] = str_replace($dest, '', $aData['d']);
                }
                $sOut .= $aData['msg'] . PHP_EOL;
                $sOut .= ($aData['s'] ? '$s: ' . $aData['s'] : '') . PHP_EOL;
                $sOut .= ($aData['d'] ? '$d: ' . $aData['d'] : '') . PHP_EOL . PHP_EOL;
            }

        }
        $sOut .= 'DONE! ------------------------------------------' . PHP_EOL;

        $sLogDir = $this->fixSlashStyle($this->destinationFolder) . $this->sDs . 'logs';
        $filename = $sLogDir . $this->sDs . 'logs.' . date('Y-m-d H;i;s') . '.txt';
        if (!$this->isDir($sLogDir)) {
            $this->mkdir($sLogDir);
        }
        if (!$fp = fopen($filename, 'a')) {
            return "Cannot open file ($filename)" . PHP_EOL;
        }
        if (fwrite($fp, $sOut) === FALSE) {
            return "Cannot write to file ($filename)" . PHP_EOL;
        }
        fclose($fp);
        return "Log saved in ($filename)" . PHP_EOL . $sOut;
    }

    /*
        function reportFolderRecursive($folderPath)
        {

            if ($this->isDir($folderPath)) {
                $folderFileAndDirArray = $this->getDirFileList($folderPath);
                if (!empty($folderFileAndDirArray)) {
                    $array = array();
                    foreach ($folderFileAndDirArray as $fileOrDir) {
                        $path = $folderPath . $this->sDs . $fileOrDir;
                        if ($this->isDir($path)) {
                            $innerFolderPath = $folderPath . $this->sDs . $fileOrDir;
                            $array[] = $this->reportFolderRecursive($innerFolderPath);
                        } else {
                            $array[] = $folderPath . $this->sDs . $fileOrDir;
                        }
                    }

                    return $array;
                }
            }
        }

        function exportDateFolder($path, $date)
        {
            $newDate = date($this->datePattern, strtotime($date));

            $createDestinationPath = $this->destinationFolder . $this->sDs . $newDate;
            if ($this->isDir($createDestinationPath)) {
                if ($this->isDir($path)) {
                    if (!$this->isDir($path . $this->sDs . $newDate)) {
                        $this->mkdir($path . $this->sDs . $newDate);
                    }

                    $dateFolderArray = $this->getDirFileList($createDestinationPath);

                    if (!empty($dateFolderArray)) {
                        foreach ($dateFolderArray as $fileOrDir) {
                            $copyFromPath = $createDestinationPath . $this->sDs . $fileOrDir;
                            $copyToPath = $path . $this->sDs . $newDate . $this->sDs . $fileOrDir;

                            if ($this->isDir($copyFromPath)) {
                                $this->recursiveExport($copyFromPath, $copyToPath);
                            } else {
                                copy($copyFromPath, $copyToPath);
                            }
                        }
                    }

                    return 'Done';
                }

                return 'Path not found';
            }

            return array();
        }

        function recursiveExport($source, $destination)
        {
            $sourceFolder = $this->getDirFileList($source);

            if (!$this->isDir($destination)) {
                $this->mkdir($destination);
            }

            foreach ($sourceFolder as $fileOrdir) {
                $copyFromPath = $source . $this->sDs . $fileOrdir;
                $copyToPath = $destination . $this->sDs . $fileOrdir;

                if ($this->isDir($source . $this->sDs . $fileOrdir)) {
                    $this->recursiveExport($copyFromPath, $copyToPath);
                } else {
                    copy($copyFromPath, $copyToPath);
                }
            }
        }
    */


}


$rd1 = 'C:\xampp\htdocs\afr';
$rd2 = 'C:\xampp\htdocs\afr';
$oBkp = new \Autoframe\Core\BackupBPG\PhpBackupClass(
    $rd1 . '\src\Arr',
    $rd2 . '\Bkp_test'
);
$oBkp->makeBackup();
//print_r($oBkp->aActionLog);
//print_r($oBkp->aFiletype);
//echo "\n------------------------------------\n\n";
?>