<?php
declare(strict_types=1);

namespace Autoframe\Core\AW;
use ReflectionClass;

class AfrAWClassBuilder
{
     public function build(string $sClassName)
     {
         $arguments = $this->getClassArguments($sClassName);
         return new $sClassName(...$arguments);
     }
     protected function getClassArguments(string $sClassName)
     {
        $class = new ReflectionClass($sClassName);
        if($class->hasMethod('__construct')){
            return [];
        }
        $params = $class->getMethod('__construct')->getParameters();
        $aServices = [];
        foreach($params as $param){
            $aServices[] = $this->build(
                $param->getClass()->name
            );
        }
        return $aServices;
     }

}