<?php

namespace Autoframe\Core\FileSystem\Traversing;


interface AfrDirTraversingCollectionInterface extends
    AfrDirTraversingCountChildrenDirsInterface,
    AfrDirTraversingFileListInterface,
    AfrDirTraversingGetAllChildrenDirsInterface,
    AfrDirTraversingSortInterface
{

}