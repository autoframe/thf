<?php
declare(strict_types=1);

namespace Autoframe\Core\Entity;

abstract class AfrEntity implements AfrEntityInterface
{
    use AfrEntityTrait;

    /**
     * @param $mProperties
     * @throws Exception\AfrEntityException
     */
    final public function __construct($mProperties = [])
    {
        if (is_array($mProperties)) {
            $this->setAssoc((array)$mProperties);
        }
        elseif (is_object($mProperties)){
            $this->copyPublicProperties($mProperties);
        }
        $this->_dirty = false;
        $this->_dirtyProperty = [];
    }
}