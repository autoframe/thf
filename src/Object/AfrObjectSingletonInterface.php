<?php

namespace Autoframe\Core\Object;

use Autoframe\Core\Exception\AutoframeException;
use ReflectionException;

interface AfrObjectSingletonInterface
{
    /**
     * @throws AutoframeException
     */
    public function __wakeup();

    /**
     * The method you use to get the Singleton's instance.
     * @return object
     * @throws ReflectionException
     */
    public static function getInstance(): object;

    /**
     * The method you use to get a new Singleton's instance.
     * @return object
     * @throws ReflectionException
     */
    public static function newInstance(): object;

    /**
     * The method you use to get a new Singleton's instance from inline arguments.
     * @return object
     * @throws ReflectionException
     */
    public static function newInstanceArgs(): object;

    /**
     * The method you use to get a new Singleton's instance from array arguments.
     * @return object
     * @throws ReflectionException
     */
    public static function newInstanceArrayOfArgs(array $arguments): object;

    /**
     * @return bool
     */
    public static function hasInstance(): bool;
}