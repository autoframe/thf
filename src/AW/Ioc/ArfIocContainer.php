<?php
declare(strict_types=1);

namespace Autoframe\Core\AW\Ioc;

use Autoframe\Core\AW\Exception\AfrContainerException;

class ArfIocContainer
{
    protected array $bindings = [];
    protected static ?ArfIocContainer $instance = null;
    protected function __construct()
    {
    }
    public static function getInstatance()
    {
        if(is_null(self::$instance)){
            self::$instance = new static();
        }
        return self::$instance;

    }

    public function bind($abstract, $concrete)
    {
        $this->bindings[$abstract] = $concrete;
    }

    public function resolve($abstract)
    {
        if (!isset($this->bindings[$abstract])) {
            $this->ensureClassIsInstantiable($abstract);
        }
        $concrete = $this->bindings[$abstract] ?? $abstract;

        if(is_callable($concrete)){
            return call_user_func($concrete);
        }
        return $this->buildInstance($concrete);
    }

    protected function ensureClassIsInstantiable($class)
    {
        $reflection  = new \ReflectionClass($class);
        if(!$reflection->isInstantiable()){
            throw new AfrContainerException("$class is not instantiable");
        }

    }

    protected function buildInstance($class)
    {
        if(isset($this->bindings[$class]) && is_callable($this->bindings[$class])){
            return call_user_func($this->bindings[$class]);
        }
        $reflection  = new \ReflectionClass($class);
        $constructor = $reflection->getConstructor();
        if(!$constructor && $reflection->isInstantiable()){
            return new $class;
        }
        if(!$constructor && isset($this->bindings[$class])){
            return $this->buildInstance($this->bindings[$class]);
        }
        if(!$constructor && !isset($this->bindings[$class])){
            throw new AfrContainerException("$class is not instantiable");
        }
        $parametersInstances = [];
        foreach($constructor->getParameters() as $parameter){
            if(!$parameter->getType() && $parameter->isOptional()){
                throw new AfrContainerException("{$parameter->getName()} for $class is not instantiable");
            }
            $parametersInstances[] = $this->buildInstance($parameter->getClass()->getName());
        }
        return new $class(...$parametersInstances);
    }

}