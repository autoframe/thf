<?php

namespace Autoframe\Core\FileSystem\Mime;

interface AfrFileMimeInterface
{
    /**
     * @return array
     */
    public function getFileMimeTypes(): array;


    /**
     * @return array
     */
    public function getFileMimeExtensions(): array;


    /**
     * @return string 'application/octet-stream'
     */
    public function getFileMimeFallback(): string;


    /**
     * wmz extension has multiple mimes
     * @param string $sFileNameOrPath
     * @return array
     */
    public function getAllMimesFromFileName(string $sFileNameOrPath): array;

    /**
     * Data: /dir/test.jpg
     * @param string $sFileNameOrPath
     * @return string
     */
    public function getMimeFromFileName(string $sFileNameOrPath): string;


    /**
     * Data: 'image/jpeg'
     * @param string $sMime
     * @return array
     */
    public function getExtensionsForMime(string $sMime): array;


}