<?php
declare(strict_types=1);

namespace Autoframe\Core\AW;

use ReflectionClass;

class AfrAwFqcnSameNs
{
    protected string $sVendorPath;
    protected string $sVendor = 'vendor';

    function getNakedClassName()
    {
    }

    function getNamespace()
    {
    }

    function getClassType()
    {
    }

    function getClassNameVariations()
    {
    } //aaaClass si aaa din aaaInterface; Incerca si class.aaa.php si aaa.class.php

    function getFindImplementationInSameNamespace()
    {
    }

    function getFindImplementationInUpperNamespaces()
    {
    }

    function getDinAfrConfig()
    {
    }

    function getDinClasaImplemented()
    {
    }//string, txt, php ??

    //props statice la cautarea pe hdd si default framework
    //posibilitatea cache?
    //instanta singleton / instanta new cu chestii statice + combinatie intre ele.
    //mapare in functie de prioritatea de gasire
    //gasire logica de instantiere clasa in aw daca se foloseste single instance / singleton sau multiple instance


    function getImplementingClasses(string $interface): array
    {
        $implementingClasses = [];

        // Use Composer's autoload to load all classes
        //require 'vendor/autoload.php';

        // Get all declared classes using Reflection
        $declaredClasses = get_declared_classes();

        // Iterate through each class and check if it implements the interface
        foreach ($declaredClasses as $class) {
            $reflectionClass = new ReflectionClass($class);

            // Check if the class implements the given interface
            if ($reflectionClass->implementsInterface($interface)) {
                $implementingClasses[] = $class;
            }
        }

        return $implementingClasses;
    }

    function getClassesImplementingInterface(string $interfaceFQCN): array
    {
        $classNames = [];
        $interfaceReflection = new \ReflectionClass($interfaceFQCN);
        $composerClassLoader = require __DIR__ . '/../../vendor/autoload.php';
        $aCm = $composerClassLoader->getClassMap();
        foreach ($aCm as $className => $classFilePath) {
            if (is_subclass_of($className, $interfaceFQCN) && !$interfaceReflection->isInterface()) {
                $classNames[] = $className;
            }
        }

        return $classNames;
    }


}