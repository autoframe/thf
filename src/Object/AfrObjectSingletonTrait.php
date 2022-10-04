<?php


namespace Autoframe\Core\Object;

use Autoframe\Core\Exception\AutoframeException;
use ReflectionClass;
use ReflectionException;
use function func_get_args;

trait AfrObjectSingletonTrait
{
    //https://refactoring.guru/design-patterns/singleton/php/example#example-1
    /**
     * The actual singleton's instance almost always resides inside a static
     * field. In this case, the static field is an array, where each subclass of
     * the Singleton stores its own instance.
     */
    private static array $instances = [];

    /**
     * Singleton's constructor should not be public. However, it can't be
     * private either if we want to allow subclassing.
     */
    protected function __construct()
    {
    }

    /**
     * Cloning and unserialization are not permitted for singletons.
     * @throws AutoframeException
     */
    protected function __clone()
    {
        throw new AutoframeException("Cannot clone a singleton");
    }

    /**
     * @throws AutoframeException
     */
    public function __wakeup()
    {
        throw new AutoframeException("Cannot unserialize singleton");
    }


    /**
     * The method you use to get the Singleton's instance.
     * @return object
     * @throws ReflectionException
     */
    public static function getInstance(): object
    {
        $subclass = static::class;
        if (!self::hasInstance()) {
            // Note that here we use the "static" keyword instead of the actual
            // class name. In this context, the "static" keyword means "the name
            // of the current class". That detail is important because when the
            // method is called on the subclass, we want an instance of that
            // subclass to be created here.
            $arguments = func_get_args();

            if ($arguments) {
                return self::newInstanceArrayOfArgs($arguments);
            } else {
                return self::$instances[$subclass] = new static();
            }
        }
        return self::$instances[$subclass];
    }

    /**
     * The method you use to get a new Singleton's instance.
     * @return object
     * @throws ReflectionException
     */
    public static function newInstance(): object
    {
        $arguments = func_get_args();
        if ($arguments) {
            return self::newInstanceArrayOfArgs($arguments);
        } else {
            return self::$instances[ static::class ] = new static();
        }
    }

    /**
     * The method you use to get a new Singleton's instance from inline arguments.
     * @return object
     * @throws ReflectionException
     */
    public static function newInstanceArgs(): object
    {
        return self::newInstanceArrayOfArgs(func_get_args());
    }

    /**
     * The method you use to get a new Singleton's instance from array arguments.
     * @return object
     * @throws ReflectionException
     */
    public static function newInstanceArrayOfArgs(array $arguments): object
    {
        $subclass = static::class;
        return self::$instances[$subclass] =
            (new ReflectionClass($subclass))->newInstanceArgs($arguments);
    }

    /**
     * @return bool
     */
    public static function hasInstance(): bool
    {
        return isset(self::$instances[static::class]);
    }

}