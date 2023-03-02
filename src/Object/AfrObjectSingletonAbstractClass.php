<?php
declare(strict_types=1);


namespace Autoframe\Core\Object;
//use \Autoframe\Core\Object\AfrObjectSingletonInterface;

abstract class AfrObjectSingletonAbstractClass implements AfrObjectSingletonInterface
{
    use AfrObjectSingletonTrait;
}