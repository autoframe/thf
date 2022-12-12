<?php
declare(strict_types=1);

namespace Autoframe\Core\Http\Header\Utils;

use Autoframe\Core\FileSystem\Mime\AfrFileMimeClass;

trait AfrHttpHeaderUtils
{

    /** @var int just the mime type and the length */
    static int $NoContentDisposition = 0;

    /** @var int attachement: If you want to encourage the client to download it instead of following the default behaviour */
    static int $ContentDispositionFileTransfer = 1;

    /** @var int attachement with application/force-download */
    static int $ContentDispositionFileTransferForceDownload = 2;

    /** @var int inline: With inline, the browser will try to open the file within the browser */
    static int $ContentDispositionInline = 3;

    /**
     * @param string $sFullFilePath
     * @param int $iKnown
     * @return void
     */
    protected function headerContentLength(string $sFullFilePath, int $iKnown = -1): void
    {
        $iSize = $iKnown > -1 ? $iKnown : filesize($sFullFilePath);
        if ($iSize > 0) {
            header('Content-Length: ' . $iSize);
        }
    }

    /**
     * 100: Continue;
     * 101: Switching Protocols;
     * 200: OK;
     * 201: Created;
     * 202: Accepted;
     * 203: Non-Authoritative Information;
     * 204: No Content;
     * 205: Reset Content;
     * 206: Partial Content;
     * 300: Multiple Choices;
     * 301: Moved Permanently;
     * 302: Moved Temporarily;
     * 303: See Other;
     * 304: Not Modified;
     * 305: Use Proxy;
     * 400: Bad Request;
     * 401: Unauthorized;
     * 402: Payment Required;
     * 403: Forbidden;
     * 404: Not Found;
     * 405: Method Not Allowed;
     * 406: Not Acceptable;
     * 407: Proxy Authentication Required;
     * 408: Request Time-out;
     * 409: Conflict;
     * 410: Gone;
     * 411: Length Required;
     * 412: Precondition Failed;
     * 413: Request Entity Too Large;
     * 414: Request-URI Too Large;
     * 415: Unsupported Media Type;
     * 500: Internal Server Error;
     * 501: Not Implemented;
     * 502: Bad Gateway;
     * 503: Service Unavailable;
     * 504: Gateway Time-out;
     * 505: HTTP Version not supported
     * @param int $iCode
     * @return bool|int
     */
    protected function headerHttpResponseCode(int $iCode)
    {
        return http_response_code($iCode);
    }

    /**
     * @param int $iTs
     * @return string
     */
    protected function getHeaderGmtDateStr(int $iTs): string
    {
        return gmdate('D, d M Y H:i:s', $iTs) . ' GMT';
    }

    /**
     * @param string $sFullFilePath
     * @param int $iKnown
     * @param string $sGmtDate
     * @return void
     */
    protected function headerLastModified(string $sFullFilePath, int $iKnown = -1, string $sGmtDate = ''): void
    {
        if (!$sGmtDate) {
            $this->getHeaderGmtDateStr($iKnown !== -1 ? $iKnown : (int)filemtime($sFullFilePath));
        }
        header('Last-Modified: ' . $sGmtDate);
    }

    /**
     * @param int $iExpires
     * @return void
     */
    protected function headerExpires(int $iExpires): void
    {
        header('Expires: ' . $this->getHeaderGmtDateStr($iExpires));
    }

    /**
     * 0: No Content-Disposition
     * 1: Content-Disposition: attachment; If you want to encourage the client to download it instead of following the default behaviour
     * 2: Content-Disposition: attachment + application/force-download
     * 3: Content-Disposition: inline: With inline, the browser will try to open the file within the browser
     * @param int $iDownloadMode
     * @param string $sSaveFileName
     * @return void
     */
    protected function headerContentDisposition(int $iDownloadMode, string $sSaveFileName = ''): void
    {
        if ($iDownloadMode === self::$NoContentDisposition) {
            return;
        }
        if (strlen($sSaveFileName)) {
            $sSaveFileName = basename($sSaveFileName);//get original filename
        }
        $sFilename = strlen($sSaveFileName) ? '; filename=' . urlencode($sSaveFileName) : '';

        if ($iDownloadMode === self::$ContentDispositionInline) {
            header('Content-Disposition: inline' . $sFilename);
        }

        if ($iDownloadMode === self::$ContentDispositionFileTransfer || $iDownloadMode === self::$ContentDispositionFileTransferForceDownload) {
            header('Content-Description: File Transfer');
            header('Content-Disposition: attachment' . $sFilename);
        }
        if ($iDownloadMode === self::$ContentDispositionFileTransferForceDownload) {
            header('Content-Type: application/force-download');
        }
    }

    /**
     * @param string $sFileNameOrPath
     * @param string $sCharset
     * @param bool $bAutodetectEncoding
     * @param array $aCharsetExtensions
     * @return void
     */
    protected function headerContentTypeMime(
        string $sFileNameOrPath,
        string $sCharset = '',
        bool   $bAutodetectEncoding = false,
        array  $aCharsetExtensions = ['html', 'js', 'css', 'csv', 'txt', 'php']
    ): void
    {
        $sContentType = (new AfrFileMimeClass())->getMimeFromFileName($sFileNameOrPath);
        $aInfo = pathinfo($sFileNameOrPath);
        if (!empty($aInfo['extension']) && in_array(strtolower($aInfo['extension']), $aCharsetExtensions)) {
            if ($sCharset) {
                $sContentType .= '; charset=' . $sCharset;
            } elseif ($bAutodetectEncoding) {
                $handle = fopen($sFileNameOrPath, 'r');
                if ($handle) {
                    $sBuffer = fgets($handle, 1024 * 8);
                    fclose($handle);
                    $sContentType .= '; charset=' . mb_detect_encoding($sBuffer, mb_list_encodings());
                }
            } else {
                $sContentType .= '; charset=utf-8';
            }
        }
        header('Content-Type: ' . $sContentType);
    }


    /**
     * The browser requested a clean page by pressing F5 or CTRL+R
     * @return bool
     */
    protected function isRefreshRequest(): bool
    {
        return (
            isset($_SERVER['HTTP_PRAGMA']) && $_SERVER['HTTP_PRAGMA'] == 'no-cache' ||
            isset($_SERVER['HTTP_CACHE_CONTROL']) && $_SERVER['HTTP_CACHE_CONTROL'] == 'no-cache' ||
            isset($_SERVER['HTTP_CACHE_CONTROL']) && $_SERVER['HTTP_CACHE_CONTROL'] == 'max-age=0'
        );
    }

    /**
     * @param string $sFullFilePath
     * @param int $iFileSize
     * @param int $iFileMtime
     * @param string $sA
     * @param string $sB
     * @param bool $bSendHeader
     * @return string
     */
    protected function headerETag(
        string $sFullFilePath,
        int    $iFileSize = -1,
        int    $iFileMtime = -1,
        string $sA = '-th-',
        string $sB = '-or-',
        bool   $bSendHeader = true
    ): string
    {
        $iFileSize = $iFileSize === -1 ? filesize($sFullFilePath) : $iFileSize;
        $iFileMtime = $iFileMtime === -1 ? filemtime($sFullFilePath) : $iFileMtime;
        $eTag =
            dechex(crc32((string)$iFileSize)) . $sA .
            dechex(crc32($sFullFilePath)) . $sB .
            dechex(crc32((string)$iFileMtime));
        if ($bSendHeader) {
            header('ETag: "' . $eTag . '"');
        }
        return $eTag;
    }

    /**
     * @param int $iExpected
     * @return bool.
     */
    public function isHttpResponseCode(int $iExpected = 200): bool
    {
        $mResponse = http_response_code();
        if (is_bool($mResponse)) { //CLI
            return false;
        } else {
            return $mResponse === $iExpected;
        }
    }
    /**
     * @return void
     */
    public function headerNoCache():void
    {
        header('Cache-Control:	no-store, no-cache, must-revalidate, post-check=0, pre-check=0'); // HTTP/1.1
        header('Pragma: no-cache');
        header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
    }

    /**
     * @param int $iSecondsToCache
     * @return void
     */
    public function headerDoCache(int $iSecondsToCache = 2592000):void
    {
        header('Pragma: cache');
        header('Cache-Control: max-age='.$iSecondsToCache);
        $this->getHeaderGmtDateStr(time() + $iSecondsToCache);
    }


}