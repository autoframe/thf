<?php
declare(strict_types=1);

//dependency injection  psr-11
// PHP-DI
//Service Containers
//ServiceContainer AKA Dependency Injection Container
// Create in PHP a class that can resolve concrete class names when
// Using a PHP 7.4 and having composer available, please create a function or class that expects as a parameter a FQCN interface and that returns an array containing classes that implement that interface.
// Using a PHP 7.4 and having composer available, please create a function that gets all the classes that can be autoloaded by composer.
// Using a PHP 7.4 and having composer available, please create a function that gets all the classes that can be autoloaded by composer, by looping the vendor directory.

namespace Autoframe\Core\AW;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Autoframe\Core\AW\Exception\AfrContainerException;
use ReflectionException;

class AfrDIContainer implements ContainerInterface
{
    protected static array $aClassMap = [];

    /**
     * @inheritDoc
     * @throws AfrContainerException|ReflectionException
     */
    public function get(string $id)
    {
        if ($this->has($id)) {
            $entry = self::$aClassMap[$id];
            if (is_callable($entry)) {
                return $entry($this);//TODO workaround mapare container in functie de clasa
            }
            $id = $entry;
        }
        return $this->resolve($id);
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     * @throws AfrContainerException
     */
    public function resolve(string $id)
    {
        $reflectionClass = new \ReflectionClass($id);
        if (!$reflectionClass->isInstantiable()) {
            throw new AfrContainerException('Class is not instantiable: ' . $id);
        }
        $constructor = $reflectionClass->getConstructor();
        if (empty($constructor)) {
            return new $id();
        }
        $parameters = $constructor->getParameters();
        if (empty($parameters)) {
            return new $id();
        }
        $dependencies = array_map(function (\ReflectionParameter $param) use ($id) {
            $name = $param->getName();
            $type = $param->getType();
            if (!$type) {
                throw new AfrContainerException('No type hint for class ' . $id . ' parameter: ' . $name);
            }
            if (version_compare(PHP_VERSION, '8.0.0', '>=')) { //php8
                if ($type instanceof \ReflectionUnionType) {
                    throw new AfrContainerException('Failed to resolve class ' . $id . ' because of union type parameter: ' . $name);
                }
            }
            if ($type instanceof \ReflectionNamedType && !$type->isBuiltin()) {
                return $this->get($type->getName());
            }
            throw new AfrContainerException('Failed to resolve class ' . $id . ' because of invalid parameter: ' . $name);
        }, $parameters);

        return $reflectionClass->newInstanceArgs($dependencies);
    }

    /**
     * @inheritDoc
     */
    public function has(string $id): bool
    {
        return isset(self::$aClassMap[$id]);
    }

    /**
     * @param string $id
     * @param callable $concrete Factory
     * @return void
     * @throws AfrContainerException
     */
    public function set(string $id, callable $concrete): void
    {
        if (!is_callable($concrete) && !is_string($concrete)) {
            throw new AfrContainerException('Container set second parameter must be callable|string');
        }
        self::$aClassMap[$id] = $concrete;
    }
}