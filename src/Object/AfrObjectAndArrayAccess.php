<?php
declare(strict_types=1);


namespace Autoframe\Core\Object;

use ArrayAccess;

/**
 * Class AfrObjectAndArrayAccess
 * @package Autoframe\Core\Object
 *
 * https://www.php.net/manual/en/class.arrayaccess.php
 */

class AfrObjectAndArrayAccess implements ArrayAccess
{
    use AfrObjectAndArrayAccessTrait;
}
