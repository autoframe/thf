<?php
declare(strict_types=1);

namespace Autoframe\Core\FileSystem\DirPath;

use Autoframe\Core\FileSystem\DirPath\Exception\AfrFileSystemDirPathException;

trait AfrDirPathTrait
{
    /**
     * the call filetype()=="dir" is clearly faster than the is_dir() call
     * @param string $sDirPath
     * @return bool
     */
    final public function dirPathIsDir(string $sDirPath): bool
    {
        //Possible values are fifo, char, dir, block, link, file, socket and unknown.
        return @filetype($sDirPath) === 'dir';
    }


    /**
     * @return string[]
     */
    final public function getDirPathDefaultSeparators(): array
    {
        return ['/', '\\'];
    }

    final public function getDirPathIsDirAlias(string $sFileName): bool
    {
        return $sFileName === '.' || $sFileName === '..';
    }

    /**
     * Validate or detect a slash style from a dir path
     * @param string $sDirPath
     * @param string $sSlashStyleFormat
     * @return string
     */
    public function dirPathValidateDetectSlashStyle(string $sDirPath, string $sSlashStyleFormat = ''): string
    {
        if ($sSlashStyleFormat === DIRECTORY_SEPARATOR) {
            return DIRECTORY_SEPARATOR;
        }
        if ($sSlashStyleFormat && !in_array($sSlashStyleFormat, $this->getDirPathDefaultSeparators())) {
            $sSlashStyleFormat = ''; // drop wrong strings
        }
        if (!$sSlashStyleFormat) {
            $sSlashStyleFormat = $this->dirPathDetectDirectorySeparator($sDirPath);
        }
        return $sSlashStyleFormat;
    }

    /**
     * Detect path slash style: /
     * @param string $sDirPath
     * @return string
     */
    public function dirPathDetectDirectorySeparator(string $sDirPath): string
    {
        foreach ($this->getDirPathDefaultSeparators() as $sDs) {
            if (strpos($sDirPath, $sDs) !== false) {
                return $sDs;
            }
        }
        return DIRECTORY_SEPARATOR;
    }

    /**
     * Make the dir path to a uniform path for cross system like windows to unix
     * @param string $sDirPath
     * @param string $sSlashFormat
     * @return string
     */
    public function dirPathCorrectSlashStyle(string $sDirPath, string $sSlashFormat = ''): string
    {
        return $this->dirPathCorrectSlashStyleMethod(
            $sDirPath,
            $this->dirPathValidateDetectSlashStyle($sDirPath, $sSlashFormat)
        );
    }

    /**
     * @param string $sDirPath
     * @param string $sSlashFormat
     * @return string
     */
    private function dirPathCorrectSlashStyleMethod(string $sDirPath, string $sSlashFormat): string
    {
        $aSearch = array_diff($this->getDirPathDefaultSeparators(), [$sSlashFormat]);
        $iTypes = count($aSearch);
        if ($iTypes) {
            foreach ($aSearch as $sDs) {
                if (strpos($sDirPath, $sDs) !== false) {
                    $aReplace = array_fill(0, $iTypes, $sSlashFormat);
                    return str_replace($aSearch, $aReplace, $sDirPath);
                }
            }
        }
        return $sDirPath;
    }

    /**
     * Add a final slash to a directory path
     * @param string $sDirPath
     * @param string $sReplaceFormat
     * @return string
     */
    public function dirPathAddFinalSlash(string $sDirPath, string $sReplaceFormat = ''): string
    {
        return $this->dirPathRemoveFinalSlash($sDirPath) . $this->dirPathValidateDetectSlashStyle($sDirPath, $sReplaceFormat);
    }

    /**
     * Remove final slash from a directory path
     * @param string $sDirPath
     * @return string
     */
    public function dirPathRemoveFinalSlash(string $sDirPath): string
    {
        return rtrim($sDirPath, implode('', $this->getDirPathDefaultSeparators()));
    }

    /**
     * Full fix for a full directory path
     * @param string $sDirPath
     * @param bool $bWithFinalSlash
     * @param bool $bCorrectSlashStyle
     * @param string $sSlashStyle
     * @return string
     */
    public function dirPathCorrectFormat(
        string $sDirPath,
        bool   $bWithFinalSlash = true,
        bool   $bCorrectSlashStyle = true,
        string &$sSlashStyle = DIRECTORY_SEPARATOR
    ): string
    {
        $sSlashStyle = $this->dirPathValidateDetectSlashStyle($sDirPath, $sSlashStyle);
        $sDirPath = $bWithFinalSlash ?
            $this->dirPathAddFinalSlash($sDirPath, $sSlashStyle) :
            $this->dirPathRemoveFinalSlash($sDirPath);
        if ($bCorrectSlashStyle) {
            $sDirPath = $this->dirPathCorrectSlashStyleMethod($sDirPath, $sSlashStyle);
        }
        return $sDirPath;
    }

    /**
     * @param string $sDirPath
     * @param $context
     * @return false|resource
     * @throws AfrFileSystemDirPathException
     */
    public function openDir(string $sDirPath, $context = null)
    {
        try {
            if ($context) {
                $resource = opendir($sDirPath, $context);
            } else {
                $resource = opendir($sDirPath);
            }
        } catch (\Exception $ex) {
            throw new AfrFileSystemDirPathException('Unable to open directory: ' . $sDirPath);
        }
        return $resource;
    }

}
