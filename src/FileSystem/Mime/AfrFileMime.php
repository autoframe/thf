<?php

namespace Autoframe\Core\FileSystem\Mime;

//TODO autoteste php unit

trait AfrFileMime
{
    use AfrFileMimeExtensions;
    use AfrFileMimeTypes;

    /**
     * @return array
     */
    public function getFileMimeTypes(): array
    {
        return self::$aAfrFileMimeTypes;
    }

    /**
     * @return array
     */
    public function getFileMimeExtensions(): array
    {
        return self::$aAfrFileMimeExtensions;
    }

    /**
     * @return string
     */
    public function getFileMimeFallback(): string
    {
        return 'application/octet-stream';
    }

    /**
     * wmz extension has multiple mimes
     * @param string $sFileNameOrPath
     * @return array
     */
    public function getAllMimesFromFileName(string $sFileNameOrPath): array
    {
        $aReturn = [];
        $sExt = $this->getFileMimeExtensionFromPath($sFileNameOrPath);
        if (!empty($sExt)) {
            foreach (self::$aAfrFileMimeTypes as $sMine => $aExtensions) {
                if (in_array($sExt, $aExtensions)) {
                    $aReturn[] = $sMine;
                }
            }
        }
        if (empty($aReturn)) {
            $aReturn[] = $this->getFileMimeFallback();
        }
        return $aReturn;
    }

    /**
     * @param string $sFileNameOrPath
     * @return string
     */
    public function getMimeFromFileName(string $sFileNameOrPath): string
    {
        $sExt = $this->getFileMimeExtensionFromPath($sFileNameOrPath);
        if (empty($sExt)) {
            return $this->getFileMimeFallback();
        } elseif (isset(self::$aAfrFileMimeExtensions[$sExt])) {
            return self::$aAfrFileMimeExtensions[$sExt];
        }
        return $this->getFileMimeFallback();
    }

    /**
     * Expected: 'image/jpeg'
     * @param string $sMime
     * @return array
     */
    public function getExtensionsForMime(string $sMime): array
    {
        if (isset(self::$aAfrFileMimeTypes[$sMime])) {
            return self::$aAfrFileMimeTypes[$sMime];
        }
        return [];
    }

    /**
     * @param string $sFileNameOrPath
     * @return string
     */
    private function getFileMimeExtensionFromPath(string $sFileNameOrPath): string
    {
        $aPath = pathinfo($sFileNameOrPath);
        return isset($aPath['extension']) && strlen($aPath['extension']) > 0 ? strtolower($aPath['extension']) : '';
    }

}