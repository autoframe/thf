<?php

namespace Autoframe\Core\FileSystem;

use Autoframe\Core\FileSystem\DirPath\AfrDirPath;
use Autoframe\Core\FileSystem\DirPath\AfrDirPathInterface;
use Autoframe\Core\FileSystem\Encode\AfrBase64EncodeFile;
use Autoframe\Core\FileSystem\Encode\AfrBase64EncodeFileInterface;
use Autoframe\Core\FileSystem\Mime\AfrFileMime;
use Autoframe\Core\FileSystem\Mime\AfrFileMimeInterface;
use Autoframe\Core\FileSystem\Traversing\AfrDirTraversingCollection;
use Autoframe\Core\FileSystem\Traversing\AfrDirTraversingCollectionInterface;
use Autoframe\Core\FileSystem\Versioning\AfrDirVersioningCollection;
use Autoframe\Core\FileSystem\Versioning\AfrDirVersioningCollectionInterface;


class AfrFileSystemClass implements
    AfrDirPathInterface,
    AfrBase64EncodeFileInterface,
    AfrFileMimeInterface,
    AfrDirTraversingCollectionInterface,
    AfrDirVersioningCollectionInterface
{
    use AfrDirPath;
    use AfrBase64EncodeFile;
    use AfrFileMime;
    use AfrDirTraversingCollection;
    use AfrDirVersioningCollection;
}
