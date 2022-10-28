<?php

namespace Autoframe\Core\FileSystem\Versioning;

use Autoframe\Core\FileSystem\Versioning\Exception\FileSystemVersioningException;

interface AfrDirVersioningDirMtimeHashInterface
{
    /**
     * @param string $sDirPath
     * @param bool $bCanThrowException
     * @return string
     * @throws FileSystemVersioningException
     */
    public function dirVersioningDirMtimeHash(string $sDirPath, bool $bCanThrowException): string;
}