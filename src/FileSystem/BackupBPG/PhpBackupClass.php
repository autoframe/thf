<?php


class PhpBackupClass
{
    public string $sourceFolder;
    public string $destinationFolder;
    public string $sLatestFolderName = '!latest';
    public array $aActionLog = [];
    public $today = null;

    public string $datePattern = 'Ymd';

    public array $aFiletype = [];
    public string $sDirPermissions = '0775';
    public $sDs = DIRECTORY_SEPARATOR;
    private array $tmp = [];

    /**
     * @param $sSourcePath
     * @param $sDestinationPath
     * @param $dToday
     */
    public function __construct($sSourcePath, $sDestinationPath, $dToday = null)
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
    public function fixSlashStyle(string $sDirPath, bool $bForceToDs = false): string
    {
        if ($bForceToDs) {
            $sW = '\\';
            $sL = '/';
            $aMap = [
                $sW => $sL,
                $sL => $sW
            ];
            $sDirPath = str_replace($aMap[$this->sDs], $this->sDs, $sDirPath);
        }
        return rtrim($sDirPath, '\/');
    }

    /**
     * @return array|false
     */
    public function getSourceFolderFileList()
    {
        if ($this->isDir($this->sourceFolder)) {
            return $this->getDirFileList($this->sourceFolder);
        }
        return false;
    }

    /**
     * @return string
     */
    public function getDestinationFolderWithFolderName(): string
    {
        return $this->fixSlashStyle($this->destinationFolder) . $this->sDs . $this->sLatestFolderName;
    }

    /**
     * @param string $sToday
     * @return string
     */
    public function getDestinationDayBackupFolderPath(): string
    {
        return $this->fixSlashStyle($this->destinationFolder) . $this->sDs . $this->today;
    }

    /**
     * @param string $dir
     * @return bool
     */
    public function isDirEmpty(string $dir): bool
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
    private function rmdir(string $sDirPath): bool
    {
        $sDirPath = $this->fixSlashStyle($sDirPath);
        if ($this->isDirEmpty($sDirPath)) {
            $bResponse = rmdir($sDirPath);
            if ($bResponse) {
                $this->aActionLog['deleted'][] = 'Removed Empty Folder ' . $sDirPath;
                unset($this->aFiletype[$sDirPath]);
            } else {
                $this->aActionLog['err'][] = 'ERROR Folder not removable ' . $sDirPath;
            }
            return $bResponse;
        }
        return false;
    }

    /**
     * @param string $sFilePath
     * @return bool
     */
    private function unlink(string $sFilePath): bool
    {
        if ($this->isFile($sFilePath)) {
            $bResponse = unlink($sFilePath);
            if ($bResponse) {
                $this->aActionLog['deleted'][] = 'Removed File ' . $sFilePath;
                unset($this->aFiletype[$sFilePath]);
            } else {
                $this->aActionLog['err'][] = 'ERROR File not removable ' . $sFilePath;
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
    private function getDestinationDateBackupFolderPathFromDestinationPath(string $sCopyToPath, bool $bMakeDir = true): string
    {
        if (empty($this->tmp[__FUNCTION__])) {
            $this->tmp[__FUNCTION__] = [
                $this->getDestinationDayBackupFolderPath(),
                strlen($this->getDestinationFolderWithFolderName())
            ];
        }
        $sCopyToDateFilePath = $this->tmp[__FUNCTION__][0] . substr($sCopyToPath, $this->tmp[__FUNCTION__][1]);
        if($bMakeDir){
            $sBackupDateFolderPath = substr($sCopyToDateFilePath, 0, -strlen(basename($sCopyToPath)) - 1);
            if (!$this->isDir($sBackupDateFolderPath)) {
                $this->mkdir($sBackupDateFolderPath);
            }
        }


        return $sCopyToDateFilePath;


    }

    public function getDestinationFolderFileList()
    {
        $destinationFolder = $this->getDestinationFolderWithFolderName();

        if (!$this->isDir($destinationFolder)) {
            if (!$this->mkdir($destinationFolder)) {
                throw new Exception('Destination Folder not available and writable: ' . $destinationFolder);
            }
        }

        return $this->getDirFileList($destinationFolder);
    }

    /**
     * @param string $sDirPath
     * @return array
     */
    public function getDirFileList(string $sDirPath): array
    {
        $aContents = [];
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
    public function filetype($sPath, bool $bForce = false)
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
    public function isDir(string $sDirPath): bool
    {
        return $this->filetype($sDirPath) === 'dir';
    }

    /**
     * @param string $sPath
     * @return bool
     */
    public function isFile(string $sPath): bool
    {
        return $this->filetype($sPath) === 'file';
    }

    /**
     * File or Directory
     * @param string $sPath
     * @return bool
     */
    public function fileExists(string $sPath): bool
    {
        return $this->isFile($sPath) || $this->isDir($sPath);
    }

    /**
     * @param string $sDestinationDir
     * @return bool
     */
    public function mkdir(string $sDestinationDir): bool
    {
        $bStatus = mkdir($sDestinationDir, $this->sDirPermissions, true);
        if ($bStatus) {
            $this->aFiletype[$sDestinationDir] = 'dir';
            $this->aActionLog['add'][] = 'Folder Created ' . $sDestinationDir;
        } else {
            $this->aActionLog['err'][] = 'ERROR Folder not created ' . $sDestinationDir;
        }
        return $bStatus;
    }

    public function makeBackup()
    {
        if (!$this->isDir($this->sourceFolder)) {
            throw new Exception('Source Folder not found. Please check your Input: ' . $this->sourceFolder);
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
        return $bSourceDestination && $bDeletedSave;

        return (count($this->aActionLog) > 0) ? $this->aActionLog : ' All files and folder already updated';

    }

    /**
     * @param $sSourceDir
     * @param $sDestinationDir
     * @return bool
     */
    private function recursiveSourceDestinationCopy($sSourceDir, $sDestinationDir): bool
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
                $this->aActionLog['skip'][] = 'Skipped because type `' . $mType . '` ' . $sCopyFromPath;
            }
        }

        return true;
    }

    /**
     * @param $sSourceDir
     * @param $sDestinationDir
     * @return bool
     */
    private function recursiveFoundInDestinationAndDeletedFromSource($sSourceDir, $sDestinationDir): bool
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

                    $bEmpty = $this->isDirEmpty($sDestinationPath);
                    $this->rmdir($sDestinationPath);
                    //$this->aActionLog['skip'][] = '!!Skipped dir because ' . ($bEmpty ? 'empty' : 'FULL') . ' ' . $sDestinationPath;
                }

            } elseif ($mType === 'file') {
                if (!$this->isFile($sSourcePath)) {
                    $this->safeKeepFilesDeletedFromSourceToDestinationDay($sSourcePath, $sDestinationPath);
                }
            } else {
                $this->aActionLog['skip'][] = 'Skipped because type `' . $mType . '` ' . $sSourcePath;
            }
        }
        return true;
    }



    public function backupReport($startDate, $endDate)
    {

        $fromDate = new DateTime($startDate);
        $toDate = new DateTime($endDate);
        $dateDiff = $toDate->diff($fromDate);

        $dateArray = [];
        for ($fromDate; $fromDate <= $toDate; $fromDate->add(new DateInterval('P1D'))) {
            $folderName = $fromDate->format($this->datePattern);
            $folderPath = $this->destinationFolder . $this->sDs . $folderName;

            if ($this->isDir($folderPath)) {
                $folderFileAndDirArray = $this->getDirFileList($folderPath);

                if (!empty($folderFileAndDirArray)) {
                    $newArray = [];
                    foreach ($folderFileAndDirArray as $fileOrDir) {
                        $innerFolderPath = $folderPath . $this->sDs . $fileOrDir;

                        if ($this->isDir($innerFolderPath)) {
                            $dateArray[$fromDate->format('Y-m-d')][] = $this->reportFolderRecursive($innerFolderPath);
                        } else {
                            $dateArray[$fromDate->format('Y-m-d')][] = $innerFolderPath;
                        }
                    }

                }
            }
        }

        $flattenArray = [];
        foreach ($dateArray as $date => $valueOrArray) {
            $flattenArray[$date] = $this->arrayFlatten($valueOrArray);
        }

        $output = '<table border="1" style="border-collapse: collapse;width:100%;">';
        $output .= '<tr>';
        $output .= '<th>Date</th>';
        $output .= '<th>Status</th>';
        $output .= '</tr>';
        foreach ($flattenArray as $date => $array) {
            $countItems = count($array) + 1;
            $output .= '<tr>';
            $output .= '<td rowspan="' . $countItems . '">' . $date . '</td>';
            $output .= '</tr>';

            foreach ($array as $value) {
                $output .= '<tr>';
                $output .= '<td>' . $value . '</td>';
                $output .= '</tr>';
            }

        }

        $output .= '<table>';

        return $output;
    }

    public function arrayFlatten($array, $return = [])
    {

        for ($x = 0; $x <= count($array); $x++) {
            if (isset($array[$x]) && is_array($array[$x])) {
                $return = $this->arrayFlatten($array[$x], $return);
            } else {
                if (isset($array[$x])) {
                    $return[] = $array[$x];
                }
            }
        }

        return $return;
    }

    private function reportFolderRecursive($folderPath)
    {

        if ($this->isDir($folderPath)) {
            $folderFileAndDirArray = $this->getDirFileList($folderPath);
            if (!empty($folderFileAndDirArray)) {
                $array = [];
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

    public function exportDateFolder($path, $date)
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

        return [];
    }

    private function recursiveExport($source, $destination)
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

    public function singleDayReport($date)
    {
        $date = new DateTime($date);
        $folderPath = $this->destinationFolder . $this->sDs . $date->format($this->datePattern);

        if ($this->isDir($folderPath)) {
            $folderFileAndDirArray = $this->getDirFileList($folderPath);
            if (!empty($folderFileAndDirArray)) {
                foreach ($folderFileAndDirArray as $fileOrDir) {
                    $innerFolderPath = $folderPath . $this->sDs . $fileOrDir;
                    if ($this->isDir($innerFolderPath)) {
                        $dateArray[$date->format('Y-m-d')][] = $this->reportFolderRecursive($innerFolderPath);
                    } else {
                        $dateArray[$date->format('Y-m-d')][] = $innerFolderPath;
                    }
                }

                $flattenArray = [];
                foreach ($dateArray as $date => $valueOrArray) {
                    $flattenArray[$date] = $this->arrayFlatten($valueOrArray);
                }

                $output = '<table border="1" style="border-collapse: collapse;width:100%;">';
                $output .= '<tr>';
                $output .= '<th>Date</th>';
                $output .= '<th>Status</th>';
                $output .= '</tr>';
                foreach ($flattenArray as $date => $array) {
                    $countItems = count($array) + 1;
                    $output .= '<tr>';
                    $output .= '<td rowspan="' . $countItems . '">' . $date . '</td>';
                    $output .= '</tr>';

                    foreach ($array as $value) {
                        $output .= '<tr>';
                        $output .= '<td>' . $value . '</td>';
                        $output .= '</tr>';
                    }

                }

                $output .= '<table>';

                return $output;
            }
        }

        return 'Folder not found: ' . $folderPath . ' or empty <pre>' . @print_r($folderFileAndDirArray, true) . '</pre>';
    }

    /**
     * @param $sFrom
     * @param $sTo
     * @return bool
     */
    protected function copy($sFrom, $sTo): bool
    {
        if (!$this->isFile($sFrom)) {
            $this->aActionLog['err'][] = 'FILE TYPE ERROR `' . $this->aFiletype[$sFrom] . '` from ' . $sFrom;
            return false;
        }
        $bOverwrite = $this->isFile($sTo);
        $bR = copy($sFrom, $sTo);
        if ($bR) {
            if ($bOverwrite) {
                $this->aActionLog['overwrite'][] = 'File overwrite from ' . $sFrom . ' to ' . $sTo;
            } else {
                $this->aActionLog['add'][] = 'File copied from ' . $sFrom . ' to ' . $sTo;
            }
            $this->aFiletype[$sTo] = 'file';
        } else {
            $this->aActionLog['err'][] = 'ERROR when copy file from ' . $sFrom . ' to ' . $sTo;
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
    protected function move($sFrom, $sTo, bool $bKeepAllCopies = false, bool $bDeleteAction = false): bool
    {
        if (!$this->isFile($sFrom)) {
            $this->aActionLog['err'][] = 'FILE TYPE ERROR `' . $this->aFiletype[$sFrom] . '` from ' . $sFrom;
            return false;
        }
        $sMsgDel = $bDeleteAction? 'Deleted From Source: ':'';
        $bOverwrite = $this->isFile($sTo);
        if ($bOverwrite && $bKeepAllCopies) {
            $bRx = rename($sTo, $sTo.'.V' . date('Hi'));
        }
        $bR = rename($sFrom, $sTo);
        if ($bR) {
            if ($bOverwrite) {
                if ($bKeepAllCopies) {
                    $this->aActionLog['add'][] = $sMsgDel.'Copy overwrite prevented ' . $sFrom . ' to ' . $sTo;
                } else {
                    $this->aActionLog['overwrite'][] = $sMsgDel.'Move overwrite from ' . $sFrom . ' to ' . $sTo;
                }

            } else {
                $this->aActionLog['add'][] = $sMsgDel.'File moved from ' . $sFrom . ' to ' . $sTo;
            }
            unset($this->aFiletype[$sFrom]);
            $this->aFiletype[$sTo] = 'file';
        } else {
            $this->aActionLog['err'][] = $sMsgDel.'ERROR when moving file from ' . $sFrom . ' to ' . $sTo;
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
    protected function safeCopyFilesFromSourceToDestination(string $sCopyFromPath, string $sCopyToPath): void
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
            //$this->aActionLog['add'][] = 'File copied from ' . $sCopyFromPath . ' to ' . $sCopyToPath;
        }
    }

    /**
     * @param string $sSourcePath
     * @param string $sDestinationPath
     * @return void
     */
    private function safeKeepFilesDeletedFromSourceToDestinationDay(string $sSourcePath, string $sDestinationPath): void
    {
        $sCopyToDateFilePath = $this->getDestinationDateBackupFolderPathFromDestinationPath($sDestinationPath);

        //move destination file to date folder and keep first version from today near 00:01 AM
        $this->move($sDestinationPath, $sCopyToDateFilePath,true,true);


    }

}

if (0) {

    $source = 'administration/api';
    $desination = 'backupXxY';
    $backup = new PhpBackupClass($source, $desination);

    if (is_array($backup->makeBackup()) && !empty($backup->makeBackup())) {

        $ul = '<ul>';
        foreach ($backup->makeBackup() as $key => $value) {
            $ul .= '<li>' . $key;
            if (is_array($value)) {
                $ul .= '<ul>';
                foreach ($value as $message) {
                    $ul .= '<li>' . $message . '</li>';
                }
                $ul .= '</ul>';
            }
            $ul .= '</li>';
        }
        $ul .= '</ul>';

        echo $ul;
    } else {
        //echo $backup->takeBackup();
    }


//$from = '2021-03-03';
//$to = '2021-03-03';
//$report = $backup->backupReport($from,$to);
//echo $report;

    $date = '2021-03-04';
    $singleDayReport = $backup->singleDayReport($date);
    echo $singleDayReport;

//$date = '2021-03-03';
//$path = '../test/';
//$exportFolder = $backup->exportDateFolder($path,$date);

//echo "<br>";
//echo $exportFolder;
}
