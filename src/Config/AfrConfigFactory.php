<?php
declare(strict_types=1);

namespace Autoframe\Core\Config;
//TODO de instantiat clase la kil

use ReflectionClass;
use ReflectionException;
use Autoframe\Core\Config\Exception\AfrConfigException;
class AfrConfigFactory
{
    /**
     * @throws ReflectionException
     * @throws AfrConfigException
     */
    public static function makeInstanceFromNsClass(string $sNamespaceClass)
    {
        if(!self::checkClassExists($sNamespaceClass)){
            return false;
        }
        $aConstructorArgs = [];
        $aAfrConfigs = AfrConfigRegister::getInstance()->getStaticConfig($sNamespaceClass);
        foreach ($aAfrConfigs as $oAfrConfig){
            if($oAfrConfig->getNamespaceAndClassOrTraitOrInterfaceOrKey() === $sNamespaceClass){
                $aConstructorArgs = $oAfrConfig->getConstructorArgs();
                break;
            }
        }
        if(empty($aConstructorArgs)){
            $oNewObject = new $sNamespaceClass();
        }
        else{
            //$oNewObject = new $sNamespaceClass($aConstructorArgs);
            $oNewObject = (new ReflectionClass($sNamespaceClass))->newInstanceArgs($aConstructorArgs);
        }
        if(
            $oNewObject instanceof AfrConfigurableInstanceInterface ||
            is_callable([$oNewObject,'applyAfrInstanceConfig'], true)
        ){
            $oNewObject->applyAfrInstanceConfig();
        }
        else{
            throw new AfrConfigException('Class '.$sNamespaceClass.' does not implement method: applyAfrInstanceConfig');
        }
        print_r($oNewObject);
        //print_r($aAfrConfigs);
        //die('XXXXXXX'.$sNamespaceClass.'XXXXXXXXXX');

        return $oNewObject;
    }
    public static function makeSingletonFromNsClass(string $sNamespaceClass, array $aNewSingletonParams = [])
    {
        return 'obbbJEct!';
    }
    public static function makeStaticFromNsClass(string $sNamespaceClass)
    {
        return 'obbbJEct!';
    }

    /**
     * @param string $sNamespaceClass
     * @return bool
     */
    private static function checkClassExists(string $sNamespaceClass):bool
    {
        return class_exists($sNamespaceClass);
    }
}