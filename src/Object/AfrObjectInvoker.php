<?php


namespace Autoframe\Core\Object;


trait AfrObjectInvoker
{

    /**
     * @param mixed $fn 'ns\class@method'|closure|function
     * @param array $params
     * @param string $namespace
     * @return bool
     */

    protected static function invokeRouteMethod($fn, array $params = [], string $namespace = ''): bool
    {
        return self::invokeMethod($fn, $params, $namespace, true) === false ? false : true;
    }


    /**
     * @param mixed $fn 'ns\class@method'|closure|function
     * @param array $params
     * @param string $namespace
     * @param bool $bForceBoolReturn
     * @return bool|mixed
     */

    protected static function invokeMethod($fn, array $params = [], string $namespace = '', bool $bForceBoolReturn = true)
    {
        if (is_callable($fn)) {
            $r = call_user_func_array($fn, $params); // Returns the return value of the callback, or FALSE on error.
            //var_dump($r);
            if ($bForceBoolReturn && !$r && $r !== false) {
                $r = true; //fix no blank return for functions to avoid 404 in router
            }
            return $r;
        } // If not, check the existence of special parameters
        elseif (is_string($fn) && stripos($fn, '@') !== false) {
            // Explode segments of given route
            list($controller, $method) = explode('@', $fn);
            // Adjust controller class if namespace has been set
            if ($namespace !== '' && stripos($fn, '\\') === false) {
                $controller = $namespace . '\\' . $controller;
            }
            // Check if class exists, if not just ignore and check if the class exists on the default namespace
            if (class_exists($controller)) {
                // First check if is a static method, directly trying to invoke it.
                // If isn't a valid static method, we will try as a normal method invocation.
                $call_user_func_array = call_user_func_array([new $controller(), $method], $params);
                if ($call_user_func_array === false) {
                    // Try to call the method as an non-static method. (the if does nothing, only avoids the notice)
                    $forward_static_call_array = forward_static_call_array([$controller, $method], $params);
                    if ($forward_static_call_array === false) {
                        return false; //class exists but method not found
                    } else {
                        return $bForceBoolReturn ? true : $forward_static_call_array;
                    }//static method was called
                } else {
                    return $bForceBoolReturn ? true : $call_user_func_array;
                }//method was called
            } else {
                return false;
            }//class does not exist
        }
        return false;//general exception
    }

}