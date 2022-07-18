<?php

namespace Autoframe\Core\FileSystem;

trait AfrDirTools
{
    private static $aDirSeparators = ['/','\\'];

    public static function detectDirPathDirectorySeparator(string $sDirPath):string
    {

    }

    public static function formatDirPath(string $sDirPath, $bAddFinalSlash = true, string $sReplaceFormat = '')
    {
        $sSlashStyle = DIRECTORY_SEPARATOR;

        if($sReplaceFormat && !in_array($sReplaceFormat,self::$aDirSeparators)){
            $sReplaceFormat = '';
        }
        if($sReplaceFormat){

        }
        else{

        }
        return rtrim($sDirPath, '\/') . ($bAddFinalSlash ? $sSlashStyle : '');

    }

    public static function getAllSubdirs(string $sFullPath, int $iCurrentLevel = 0, int $iMaxLevels = 1)
    {
        if (
            $iCurrentLevel < 0 ||
            $iMaxLevels <= $iCurrentLevel ||
            !is_dir($sFullPath) ||
            !is_readable($sFullPath)
        ) {
            return false;
        }
        $sFullPath = rtrim($sFullPath, '\/'); //remove ending /

        $iCurrentLevel++;
        $myDirectory = opendir($sFullPath);    // open this directory
        $dirArray = array();
        while ($entryName = readdir($myDirectory)) {
            if (filetype($sFullPath . $entryName) == 'dir' && $entryName != '.' && $entryName != '..') {
                //			echo $entryName."\r\n";
                $dirArray[$entryName] = self::getAllSubdirs(
                    $sFullPath . $entryName . DIRECTORY_SEPARATOR,
                    $iCurrentLevel,
                    $iMaxLevels
                );
            }
        }
        closedir($myDirectory);    // close directory
        if (is_array($dirArray)) {
            ksort($dirArray);
        }
        return $dirArray;
    }

    public static function getDirFileList(
        string $sDirPath,
        array $aFilterExtensions = array()
    )
    {
        if (!is_dir($sDirPath) || !is_readable($sDirPath)) {
            return false;
        }
        $myDirectory = opendir($sDirPath);
        $files = array();
        while ($entry = readdir($myDirectory)) {
            $tf = rtrim($sDirPath, '\/') . DIRECTORY_SEPARATOR . $entry;
            if ($entry != '.' && $entry != '..' && is_file($tf) && is_readable($tf)) {
                if (is_array($aFilterExtensions) && count($aFilterExtensions)) {
                    foreach ($aFilterExtensions as $filter) {
                        if (substr(strtolower($entry), -strlen($filter)) == strtolower($filter)) {
                            $files[] = $entry;
                            break;
                        }
                    }
                } else {
                    $files[] = $entry;
                }
            }
        }
        closedir($myDirectory);
        natsort($files);
        return $files;
    }

    public static function countSubdirs(string $dirpath)
    {
        if (!is_dir($dirpath) || !is_readable($dirpath)) {
            return false;
        }
        $dirCount = 0;
        $myDirectory = opendir($dirpath);    // open this directory
        while ($entryName = readdir($myDirectory)) {
            $tf = rtrim($dirpath, '\/') . DIRECTORY_SEPARATOR . $entryName;

            if (filetype($tf) == 'dir' && $entryName != '.' && $entryName != '..') {
                $dirCount++;
            }
        }
        closedir($myDirectory);
        return $dirCount;
    }


    public static function thf_vers_date($time = NULL, $mod = 1)
    {
        if (!$time) {
            $time = time();
        }
        if ($mod == 1) {
            return date('y.m.d', $time) . chr((date('H', $time) * 60 + date('i', $time)) * (25 / (60 * 24)) + 65);
        } elseif ($mod == 2) {
            return date('y.m.d', $time) . chr((date('H', $time) * 60 + date('i', $time)) * (25 / (60 * 24)) + 65) . chr(date('s', $time) * (25 / 59) + 65);
        }//sec
        else {
            return date('Y-m-d H:i:s', $time);
        }
    }

    public static function dir_vers($dir_path, $return_timestamp = false, $max_subdirs = 1, $date_mod = 1)
    {
        $v = 0;
        if (is_array($dir_path) && count($dir_path)) {//array of file and dir paths
            foreach ($dir_path as $i => $path) {
                $v = max($v, dir_vers($path, true, $max_subdirs));
            }
        } elseif (is_file($dir_path) && is_readable($dir_path)) {
            $v = filemtime($dir_path);
        } elseif (is_dir($dir_path) && is_readable($dir_path)) {
            $myDirectory = opendir($dir_path);
            while ($entry = readdir($myDirectory)) {
                $tf = rtrim($dir_path, '/') . '/' . $entry;
                if ($entry != '.' && $entry != '..' && is_file($tf) && is_readable($tf)) {
                    $mtime = filemtime($tf);
                    $v = max($v, $mtime);
                } elseif ($entry != '.' && $entry != '..' && is_readable($tf) && is_dir($tf) && $max_subdirs > 0) {
                    $v = max($v, dir_vers($tf, true, ($max_subdirs - 1)));
                }
            }
            closedir($myDirectory);
        }
        if ($return_timestamp) {
            return $v;
        }
        return thf_vers_date($v, $date_mod);
    }

    public static function dir_hash($dir_path, $complex = 0, $max_subdirs = 1)
    {
        $v = NULL;
        if (is_array($dir_path)) {//array of file and dir paths
            if (count($dir_path)) {
                foreach ($dir_path as $i => $path) {
                    $v .= dir_hash($path, $complex, $max_subdirs);
                }
            }
        } elseif (is_file($dir_path) && is_readable($dir_path)) {
            if ($complex == 1) {
                $v = md5(file_get_contents($dir_path));
            } else {
                $v = filesize($dir_path) . basename($dir_path);
            }
        } elseif (is_dir($dir_path) && is_readable($dir_path)) {
            $myDirectory = opendir($dir_path);
            while ($entry = readdir($myDirectory)) {
                $tf = rtrim($dir_path, '/') . '/' . $entry;
                if ($entry != '.' && $entry != '..' && is_file($tf) && is_readable($tf)) {
                    $v .= dir_hash($tf, $complex);
                } elseif ($entry != '.' && $entry != '..' && is_readable($tf) && is_dir($tf) && $max_subdirs > 0) {
                    $v .= dir_hash($tf, $complex, $max_subdirs - 1);
                }
            }
            closedir($myDirectory);
        }

        if ($v) {
            return md5($v);
        }
        return NULL;
    }


}
