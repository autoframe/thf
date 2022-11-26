<?php

namespace Autoframe\Core\FileSystem\Traversing;

use Autoframe\Core\FileSystem\Traversing\Exception\AfrFileSystemTraversingException;

/**
 * Global static settings that have a higher priority then instance settings
 * To clear them, run: setAfrDirTraversingSortMethod(true,null,null);
 */
interface AfrDirTraversingSortInterface
{
    /**
     * @param $mFunction
     * @param array $aOptionalArgs
     * @param bool $bGlobal
     * @return void
     * @throws AfrFileSystemTraversingException
     */
    public function setAfrDirTraversingSortMethod(bool $bGlobal = false, $mDirectionOrCallableFn = SORT_ASC, int  $flags = SORT_NATURAL): void;
}