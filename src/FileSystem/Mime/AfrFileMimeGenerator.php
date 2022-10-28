<?php

namespace Autoframe\Core\FileSystem\Mime;

use Autoframe\Core\FileSystem\Mime\Exception\FileSystemMimeException;
use function file_get_contents;
use function file_put_contents;
use function filemtime;
use function is_file;
use function is_array;
use function ksort;
use function trim;
use function str_replace;
use function substr;
use function explode;
use function strtolower;
use function count;
use function basename;
use function implode;

/**
 * Utility reads the file 'mime.types' and updates the traits AfrFileMimeExtensions and AfrFileMimeTypes
 */
trait AfrFileMimeGenerator
{
    private string $sGeneratorMimeTypesPath = __DIR__ . DIRECTORY_SEPARATOR . 'mime.types';

    /**
     * @return array[]
     */
    private function AfrFileMimeGeneratorParseMimeTypes(string $sFileContents): array
    {
        $sFileContents = str_replace("\r", "\n", $sFileContents);
        // Because someone does not deem worthy this mime types, I do:
        $sFileContents .= "\napplication/x-httpd-php 	php php3 php4 php5 php6";
        $sFileContents .= "\napplication/x-httpd-php-source 	phps";

        $aFileMimeTypes = $aFileMimeExtensions = [];
        foreach (explode("\n", $sFileContents) as $sLine) {
            if (empty($sLine)) {
                continue;
            }
            $sLine = trim(str_replace("\t", ' ', $sLine));
            if (substr($sLine, 0, 1) == '#') {
                continue; // skip comments
            }
            $sMime = '';
            $aExt = [];
            foreach (explode(' ', $sLine) as $sPart) {
                if ($sPart) {
                    if (!$sMime && strpos($sPart, '/') !== false) {
                        $sMime = $sPart;
                    } else {
                        $aExt[] = strtolower($sPart);
                    }
                }
            }
            if ($sMime && !empty($aExt)) {
                $aFileMimeTypes[$sMime] = $aExt;
                foreach ($aExt as $sExt) {
                    $aFileMimeExtensions[$sExt] = $sMime;
                }
            }
        }
        ksort($aFileMimeTypes);
        ksort($aFileMimeExtensions);
        return array($aFileMimeExtensions, $aFileMimeTypes);
    }


    private function getUpdatedMimeTypesFromRepo(
        string $sUrl = 'https://svn.apache.org/repos/asf/httpd/httpd/trunk/docs/conf/mime.types',
        int $iCacheFor = 3600 * 24 * 365
    ): bool
    {
        $bIsFile = is_file($this->sGeneratorMimeTypesPath);
        if (
            $bIsFile &&
            filemtime($this->sGeneratorMimeTypesPath) + $iCacheFor > time()
        ) {
            return false;
        }
        $sRawData = file_get_contents($sUrl);
        list($aFileMimeExtensions, $aFileMimeTypes) = $this->AfrFileMimeGeneratorParseMimeTypes($sRawData);
        if (count($aFileMimeExtensions) < 500 || count($aFileMimeTypes) < 500) {
            return false;
        }
        if($bIsFile){
            rename($this->sGeneratorMimeTypesPath, $this->sGeneratorMimeTypesPath .'.bk'. time());
        }
        return file_put_contents(
            $this->sGeneratorMimeTypesPath,
            '# '.$sUrl.' '.gmdate("D, d M Y H:i:s \G\M\T")."\n".
            $sRawData
        );
    }

    /**
     * This method reads the file 'mime.types' and updates the traits AfrFileMimeExtensions and AfrFileMimeTypes
     * @param int $iDeltaTs
     * @return int
     * @throws FileSystemMimeException
     */
    public function initFileMimeParseMimeTypes(int $iDeltaTs = 60): int
    {

        if (!is_file($this->sGeneratorMimeTypesPath)) {
            throw new FileSystemMimeException('Config file is missing: ' . $this->sGeneratorMimeTypesPath);
        }
        $iMimeTypesTs = filemtime($this->sGeneratorMimeTypesPath);
        $bUpToDate = true;
        foreach (['AfrFileMimeExtensions', 'AfrFileMimeTypes'] as $sClassName) {
            if (
                !is_file(__DIR__ . DIRECTORY_SEPARATOR . $sClassName . '.php') ||
                $iMimeTypesTs > filemtime(__DIR__ . DIRECTORY_SEPARATOR . $sClassName . '.php') + $iDeltaTs
            ) {
                $bUpToDate = false;
            }
        }
        if ($bUpToDate) {
            return $iMimeTypesTs;
        }

        list($aFileMimeExtensions, $aFileMimeTypes) = $this->AfrFileMimeGeneratorParseMimeTypes(
            file_get_contents($this->sGeneratorMimeTypesPath)
        );

        if (count($aFileMimeExtensions) < 500 || count($aFileMimeTypes) < 500) {
            throw new FileSystemMimeException('Parse file failed: ' . $this->sGeneratorMimeTypesPath);
        }
        if (!$this->initFileMimeParseMimePhp("AfrFileMimeExtensions", $aFileMimeExtensions)) {
            throw new FileSystemMimeException('Unable to write the file: ' . __DIR__ . '/AfrFileMimeExtensions.php');
        }
        if (!$this->initFileMimeParseMimePhp("AfrFileMimeTypes", $aFileMimeTypes)) {
            throw new FileSystemMimeException('Unable to write the file: ' . __DIR__ . '/AfrFileMimeTypes.php');
        }

        return $iMimeTypesTs;
    }

    /**
     * @param string $sClass
     * @param array $aData
     * @return false|int
     */
    private function initFileMimeParseMimePhp(string $sClass, array $aData)
    {
        $sTrait = "<?php\nnamespace " . __NAMESPACE__ . ";\n";
        $sTrait .= "//Updated in " . __NAMESPACE__ . '\\' . basename(__FILE__) . '->' . __FUNCTION__ . " based on mime.types\n";
        $sTrait .= "trait $sClass {\n";
        $sTrait .= 'public static array $a' . $sClass . " = [\n";
        foreach ($aData as $sKey => $mVal) {
            if (is_array($mVal)) {
                $sTrait .= "'$sKey' => ['" . implode("','", $mVal) . "'],\n";
            } else {
                $sTrait .= "'$sKey' => '$mVal',\n";
            }
        }
        $sTrait .= "];\n}";
        return file_put_contents(__DIR__ . DIRECTORY_SEPARATOR . $sClass . '.php', $sTrait);
    }

}