<?php

namespace Autoframe\Core\FileSystem;

use Autoframe\Core\FileSystem\DirPath\AfrDirPathTrait;
use Autoframe\Core\FileSystem\DirPath\AfrDirPathInterface;
use Autoframe\Core\FileSystem\Encode\AfrBase64EncodeFileTrait;
use Autoframe\Core\FileSystem\Encode\AfrBase64EncodeFileInterface;
use Autoframe\Core\FileSystem\Traversing\AfrDirTraversingCollectionTrait;
use Autoframe\Core\FileSystem\Traversing\AfrDirTraversingCollectionInterface;
use Autoframe\Core\FileSystem\Versioning\AfrDirVersioningCollectionTrait;
use Autoframe\Core\FileSystem\Versioning\AfrVersioningCollectionInterface;
use Autoframe\Components\FileMime\AfrFileMimeTrait;
use Autoframe\Components\FileMime\AfrFileMimeInterface;

class AfrCollectionInterfaceSystemCollectionClass implements
    AfrDirPathInterface,
    AfrBase64EncodeFileInterface,
    AfrFileMimeInterface,
    AfrDirTraversingCollectionInterface,
    AfrVersioningCollectionInterface
{
    use AfrDirPathTrait;
    use AfrBase64EncodeFileTrait;
    use AfrFileMimeTrait;
    use AfrDirTraversingCollectionTrait;
    use AfrDirVersioningCollectionTrait;
}
