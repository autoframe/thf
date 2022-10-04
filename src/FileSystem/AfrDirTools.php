<?php

namespace Autoframe\Core\FileSystem;

use Autoframe\Core\FileSystem\Encode\AfrFileEncode;
use Autoframe\Core\FileSystem\Traversing\AfrDirTraversingCollection;
use Autoframe\Core\FileSystem\Versioning\AfrDirVersioningCollection;

trait AfrDirTools
{
    use AfrDirTraversingCollection;
    use AfrDirVersioningCollection;
    use AfrFileEncode;
}
