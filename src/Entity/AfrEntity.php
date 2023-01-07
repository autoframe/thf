<?php
declare(strict_types=1);

namespace Autoframe\Core\Entity;

abstract class AfrEntity implements AfrEntityInterface
{
    use AfrEntityTrait;

    /** Array | Object
     * @param $aProperties
     */
    final public function __construct($aProperties = [])
    {
        if ($aProperties) {
            $this->setAssoc((array)$aProperties);
        }
        $this->_dirty = false;
        $this->_dirtyProperty = [];
    }
}