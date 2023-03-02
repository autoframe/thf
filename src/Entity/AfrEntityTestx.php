<?php
declare(strict_types=1);

namespace Autoframe\Core\Entity;

class AfrEntityTestx extends AfrEntity implements AfrEntityInterface
{
    public int $iId;
    public int $iPid;
    public string $sText='DefaultText';
    public string $sPoveste='Story';
}