<?php


namespace Autoframe\Core\Exception;
use Exception;



class AutoframeException extends Exception
{

    // Redefine the exception so message isn't optional
    public function __construct($message, $code = 0, Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
    }

    // custom string representation of object
    public function __toStringx() {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }

    public function customFunction() {
        echo "A custom function for this type of exception\n";
    }

}