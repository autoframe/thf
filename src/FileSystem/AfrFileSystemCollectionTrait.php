<?php
declare(strict_types=1);

namespace Autoframe\Core\FileSystem;

use Autoframe\Core\FileSystem\DirPath\AfrDirPathTrait;
use Autoframe\Core\FileSystem\Encode\AfrBase64EncodeFileTrait;
use Autoframe\Core\FileSystem\Traversing\AfrDirTraversingCollectionTrait;
use Autoframe\Core\FileSystem\Versioning\AfrDirVersioningCollectionTrait;
use Autoframe\Components\FileMime\AfrFileMimeTrait;


trait AfrFileSystemCollectionTrait
{
    use AfrDirPathTrait;
    use AfrBase64EncodeFileTrait;
    use AfrFileMimeTrait;
    use AfrDirTraversingCollectionTrait;
    use AfrDirVersioningCollectionTrait;
}
