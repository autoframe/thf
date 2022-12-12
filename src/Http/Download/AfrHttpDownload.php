<?php
declare(strict_types=1);

namespace Autoframe\Core\Http\Download;

use Autoframe\Core\Http\Download\Exception\AfrHttpDownloadException;
use Autoframe\Core\Http\Header\AfrHttpHeader;
use Autoframe\Core\Http\Header\Utils\AfrHttpHeaderUtils;

trait AfrHttpDownload
{
    use AfrHttpHeaderUtils;
    use AfrHttpHeader;

    /**
     * 0: no Content-Disposition;
     * 1: Content-Disposition: attachment If you want to encourage the client to download it instead of following the default behaviour;
     * 2: Content-Disposition: attachment + application/force-download;
     * 3: Content-Disposition: inline: With inline, the browser will try to open the file within the browser;
     * For example, if you have a PDF file and Firefox/Adobe Reader, an inline disposition will open the PDF within Firefox,
     * whereas attachment will force it to download. If you're serving a .ZIP file, browsers won't be able
     * to display it inline, so for inline and attachment dispositions, the file will be downloaded.
     * @param int $iDownloadMode
     * @param string $sFullFilePath
     * @param string $sSaveFileName
     * @param bool $bExit
     * @param $contextRes
     * @return false|int|void
     * @throws AfrHttpDownloadException
     */
    function httpDownloadFile(
        int    $iDownloadMode,
        string $sFullFilePath,
        string $sSaveFileName = '',
        bool   $bExit = true,
               $contextRes = null
    )
    {
        if (!is_readable($sFullFilePath)) {
            $this->headerHttpResponseCode(404);
            throw new AfrHttpDownloadException('File is not readable: ' . $sFullFilePath);
        }
        $sSaveFileName = strlen($sSaveFileName) ? trim($sSaveFileName) : basename($sFullFilePath);

        $this->headerContentTypeMime($sFullFilePath);
        $this->headerContentLength($sFullFilePath);
        $this->headerLastModified($sFullFilePath);
        $this->headerContentDisposition($iDownloadMode, $sSaveFileName);
        $r = $contextRes ? readfile($sFullFilePath, false, $contextRes) : readfile($sFullFilePath);
        return $bExit ? die() : $r;
    }


    /**
     * @param string $sFileName_OR_FullFilePath
     * @param int $iCacheExpire
     * @param bool $bImmutable
     * @param bool $bMustRevalidate
     * @param bool $bExit
     * @param $contextRes
     * @return bool|int|null
     * @throws AfrHttpDownloadException
     */
    public function httpFileCache(
        string $sFileName_OR_FullFilePath,
        int    $iCacheExpire = 2678400,
        bool   $bImmutable = true,
        bool   $bMustRevalidate = true,
        bool   $bExit = true,
               $contextRes = null
    )
    {
        return $this->httpFileCacheMixedParams(
            $sFileName_OR_FullFilePath,
            $bImmutable,
            $bMustRevalidate,
            $iCacheExpire,
            null,
            -1,
            $bExit,
            $contextRes
        );
    }

    /**
     * @param string $sFileContents
     * @param string $sFileNameAndMime
     * @param int $iCacheExpire
     * @param int $iContentsMtime
     * @param bool $bImmutable
     * @param bool $bMustRevalidate
     * @param bool $bExit
     * @return bool|int|null
     * @throws AfrHttpDownloadException
     */
    public function httpStreamData(
        string $sFileContents,
        string $sFileNameAndMime,
        int    $iCacheExpire = 2678400,
        int    $iContentsMtime = -1,
        bool   $bImmutable = true,
        bool   $bMustRevalidate = true,
        bool   $bExit = true
    )
    {
        return $this->httpFileCacheMixedParams(
            $sFileNameAndMime,
            $bImmutable,
            $bMustRevalidate,
            $iCacheExpire,
            $sFileContents,
            $iContentsMtime,
            $bExit,
            null
        );

    }


    /**
     * @param int $iCacheExpire
     * @param bool $bImmutable
     * @param bool $bMustRevalidate
     * @return void
     */
    private function httpHeaderCacheControlAndExpire(
        int  &$iCacheExpire = 2678400,
        bool $bImmutable = true,
        bool $bMustRevalidate = true
    ): void
    {
        //https://www.keycdn.com/blog/cache-control-immutable
        if ($iCacheExpire < 1) {
            $iCacheExpire = 0;
            $sCacheControl = 'private';
        } else {
            $sCacheControl = 'public';
            $iCacheExpire = max($iCacheExpire, 1);//at least 1 min
        }

        if (!$bImmutable && $this->isRefreshRequest()) { //the browser requested a clean page
            $sCacheControl = 'private';
            $iCacheExpire = 0;
        }

        header(
            'Cache-Control: ' .
            $sCacheControl .
            ', max-age=' . $iCacheExpire .
            ($bMustRevalidate ? ', must-revalidate' : '') .
            ($bImmutable ? ', immutable' : '')
        );
        if (isset($_SERVER['SERVER_PROTOCOL'])) {
            $fProtocol = floatval(trim($_SERVER['SERVER_PROTOCOL'], 'HTP/ '));
            if ($fProtocol < 2) {
                header('Pragma: ' . ($sCacheControl == 'private' ? 'no-cache' : 'cache'));
            }
        }
        $this->headerExpires(time() + $iCacheExpire);

    }


    /**
     * @param string $sFileName_OR_FullFilePath
     * @param string|null $sFileContents
     * @param int $iFileMtime
     * @return array
     * @throws AfrHttpDownloadException
     */
    private function httpComputeFileSizeMtime(
        string $sFileName_OR_FullFilePath,
        string $sFileContents = null,
        int    $iFileMtime = -1
    ): array
    {
        if (is_null($sFileContents) && !is_readable($sFileName_OR_FullFilePath)) {
            $this->headerHttpResponseCode(404);
            throw new AfrHttpDownloadException('File is not readable: ' . $sFileName_OR_FullFilePath);
        }
        if (!is_null($sFileContents)) {
            $iFileSize = strlen($sFileContents);
            if ($iFileMtime === -1) {
                $iFileMtime = time() - 300;
            }
        } else {
            $iFileSize = filesize($sFileName_OR_FullFilePath);
            $iFileMtime = filemtime($sFileName_OR_FullFilePath);
        }
        return [$iFileSize, $iFileMtime];
    }

    /**
     * @param string $sFileName_OR_FullFilePath
     * @param bool $bImmutable
     * @param bool $bMustRevalidate
     * @param int $iCacheExpire
     * @param string|null $sFileContents
     * @param int $iContentsMtime
     * @param bool $bExit
     * @param $contextRes
     * @return bool|int|void
     * @throws AfrHttpDownloadException
     */
    private function httpFileCacheMixedParams(
        string $sFileName_OR_FullFilePath,
        bool   $bImmutable = true,
        bool   $bMustRevalidate = true,
        int    $iCacheExpire = 2678400,
        string $sFileContents = null,
        int    $iContentsMtime = -1,
        bool   $bExit = true,
               $contextRes = null
    )
    {
        list($iFileSize, $iFileMtime) = $this->httpComputeFileSizeMtime($sFileName_OR_FullFilePath, $sFileContents, $iContentsMtime);
        $this->httpHeaderCacheControlAndExpire($iCacheExpire, $bImmutable, $bMustRevalidate);
        $eTag = $iCacheExpire || $bImmutable ? $this->headerETag($sFileName_OR_FullFilePath, $iFileSize, $iFileMtime) : '';
        $sGmtLastModified = $this->getHeaderGmtDateStr($iFileMtime);
        $this->headerLastModified('', -1, $sGmtLastModified);

        $headers = $this->getServerRequestHeaders();
        $bIs304 = !$this->isRefreshRequest() &&
            isset($headers['If-Modified-Since']) &&
            $headers['If-Modified-Since'] == $sGmtLastModified &&
            isset($headers['If-None-Match']) &&
            $headers['If-None-Match'] == '"' . $eTag . '"';
        $return = true;

        if (!$bIs304) {
            //header('Age: '.(time()-$iFileMtime));
            $this->headerContentTypeMime($sFileName_OR_FullFilePath, '', true);
            $this->headerContentLength('', $iFileSize);
            if (!is_null($sFileContents)) {
                echo $sFileContents;
            } else {
                $return = $contextRes ? readfile($sFileName_OR_FullFilePath, false, $contextRes) : readfile($sFileName_OR_FullFilePath);
            }

        } else {
            $this->headerHttpResponseCode(304);
            $this->headerContentLength('', 0);
        }
        return $bExit ? die() : $return;
    }


}