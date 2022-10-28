<?php

namespace Autoframe\Core\FileSystem;

use Autoframe\Core\FileSystem\DirPath\AfrDirPath;
use Autoframe\Core\FileSystem\Encode\AfrBase64EncodeFile;
use Autoframe\Core\FileSystem\Mime\AfrFileMime;
use Autoframe\Core\FileSystem\Traversing\AfrDirTraversingCollection;
use Autoframe\Core\FileSystem\Versioning\AfrDirVersioningCollection;


trait AfrFileSystem
{
    use AfrDirPath;
    use AfrBase64EncodeFile;
    use AfrFileMime;
    use AfrDirTraversingCollection;
    use AfrDirVersioningCollection;
}
