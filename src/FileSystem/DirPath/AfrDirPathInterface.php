<?php

namespace Autoframe\Core\FileSystem\DirPath;

use Autoframe\Core\FileSystem\DirPath\Exception\AfrFileSystemDirPathException;

interface AfrDirPathInterface
{
    /**
     * the call filetype()=="dir" is clearly faster than the is_dir() call
     * @param string $sDirPath
     * @return bool
     */
    public function dirPathIsDir(string $sDirPath): bool;

    /**
     * @return string[]
     */
    public function getDirPathDefaultSeparators(): array;

    /**
     * @param string $sFileName
     * @return bool
     */
    public function getDirPathIsDirAlias(string $sFileName): bool;

    /**
     * Validate or detect a slash style from a dir path
     * @param string $sDirPath
     * @param string $sSlashStyleFormat
     * @return string
     */
    public function dirPathValidateDetectSlashStyle(string $sDirPath, string $sSlashStyleFormat = ''): string;

    /**
     * Detect path slash style: /
     * @param string $sDirPath
     * @return string
     */
    public function dirPathDetectDirectorySeparator(string $sDirPath): string;

    /**
     * Make the dir path to a uniform path for cross system like windows to unix
     * @param string $sDirPath
     * @param string $sSlashFormat
     * @return string
     */
    public function dirPathCorrectSlashStyle(string $sDirPath, string $sSlashFormat = ''): string;

    /**
     * Add a final slash to a directory path
     * @param string $sDirPath
     * @param string $sReplaceFormat
     * @return string
     */
    public function dirPathAddFinalSlash(string $sDirPath, string $sReplaceFormat = ''): string;

    /**
     * Remove final slash from a directory path
     * @param string $sDirPath
     * @return string
     */
    public function dirPathRemoveFinalSlash(string $sDirPath): string;

    /**
     * Full fix for a full directory path
     * @param string $sDirPath
     * @param bool $bWithFinalSlash
     * @param bool $bCorrectSlashStyle
     * @param string $sSlashStyle
     * @return string
     */
    public function dirPathCorrectFormat(string $sDirPath, bool $bWithFinalSlash = true, bool $bCorrectSlashStyle = true, string &$sSlashStyle = DIRECTORY_SEPARATOR): string;

    /**
     * @param string $sDirPath
     * @param $context
     * @return false|resource
     * @throws AfrFileSystemDirPathException
     */
    public function openDir(string $sDirPath, $context = null);
}