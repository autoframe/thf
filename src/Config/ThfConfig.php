<?php

namespace Autoframe\Core\Config;


//https://refactoring.guru/design-patterns/singleton/php/example#example-1
use Autoframe\Core\Object\AfrObjectSingletonAbstractClass;

/**
 * Applying the Singleton pattern to the configuration storage is also a common
 * practice. Often you need to access application configurations from a lot of
 * different places of the program. Singleton gives you that comfort.
 *
 * thfConfig::getInstance()->setValue('DEBUG', 1);
 * thfConfig::getInstance()->is_debug()
 * $config = Config::getInstance();
 * $config->setValue('DEBUG', 1);
 */
class ThfConfig extends AfrObjectSingletonAbstractClass
//class ThfConfig extends thfSingleton
{
    private $hashmap = ['DEBUG' => 1];

    public function getValue($key)
    {
        return $this->hashmap[$key];
    }

    public function setValue($key, $value)
    {
        $this->hashmap[$key] = $value;
    }

    public function is_production()
    {
        if ($this->hashmap['DEBUG'] == 'production' || $this->hashmap['DEBUG'] === 0 || $this->hashmap['DEBUG'] === false || $this->hashmap['DEBUG'] === null) {
            return true;
        }
        return false;
    }

    public function is_debug()
    {
        return $this->is_production() ? false : true;
    }

}