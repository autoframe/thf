<?php

namespace Autoframe\Core\FileSystem\Versioning;

use Autoframe\Core\FileSystem\Versioning\Exception\AfrFileSystemVersioningException;

interface AfrDirVersioningDirMtimeHashInterface
{
    /**
     * @param string $sDirPath
     * @param bool $bCanThrowException
     * @return string
     * @throws AfrFileSystemVersioningException
     */
    public function dirVersioningDirMtimeHash(string $sDirPath, bool $bCanThrowException): string;
}