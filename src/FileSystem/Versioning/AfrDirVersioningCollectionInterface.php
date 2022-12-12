<?php

namespace Autoframe\Core\FileSystem\Versioning;

interface AfrDirVersioningCollectionInterface extends
    AfrDirVersioningDirMtimeHashInterface,
    AfrDirMaxFileMtimeInterface
{
}