<?php
declare(strict_types=1);

namespace Autoframe\Core\Entity;

class AfrEntityTest extends AfrEntity implements AfrEntityInterface
{
    public int $iId;
    public int $iPid=0;
    public string $sText='DefaultText';
    public string $tDate;
}