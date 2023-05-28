<?php
declare(strict_types=1);

namespace Autoframe\Core\AfrComposerClassMap;

use Autoframe\Components\Arr\Export\AfrArrExportArrayAsStringClass;
use Autoframe\Components\Exception\AfrException;
use Autoframe\ClassDependency\AfrClassDependency;

class AfrMultiClassMapper
{
    use AfrMultiClassMapperTraitBase;
    use AfrMultiClassMapperTraitVendor;

    private static AfrConfigMapWiringPaths $oAfrMultiClassPaths;
    private static string $sCacheDir;
    protected static string $sNsClassFilesMap = 'NsClassFilesMap.php';
    protected static string $sInterfaceToConcrete = 'InterfaceToConcrete.php';
    protected static bool $bRegenerateAll = false;
    protected static array $aRegenerated = [];
    protected static bool $bclassTreeInfoCacheWriteDone = true;
    protected static array $aNsCl = [];


    public static function setMultiPaths(AfrConfigMapWiringPaths $oAfrMultiClassPaths): void
    {
        self::$oAfrMultiClassPaths = $oAfrMultiClassPaths;
        self::$aNsCl = self::$aRegenerated = [];
    }

    public static function xetCacheDir(string $sCacheDir = ''): string
    {
        if (strlen($sCacheDir)) { //set
            $sCacheDir = (string)realpath($sCacheDir);
            if (strlen($sCacheDir)) {
                self::$sCacheDir = $sCacheDir;
            }
        }

        if (empty(self::$sCacheDir)) { //fallback
            self::$sCacheDir = (string)realpath(__DIR__) . DIRECTORY_SEPARATOR . 'cache';
        }
        return self::$sCacheDir; //get
    }

    public static function xetRegenerateAll(bool $bRegenerateAll = null): bool
    {
        if ($bRegenerateAll !== null) { //set
            self::$bRegenerateAll = $bRegenerateAll;
        }
        return self::$bRegenerateAll; //get
    }

    public static function doMagic()
    {
        $aNsClasses = self::getNsCalssAllPathsMap();
        if (self::$aRegenerated) {
            self::$bclassTreeInfoCacheWriteDone = false;
        }
        //self::resummableClassTreeInfoCacheWrite();
        return [$aNsClasses, self::classTreeInfoCacheWrite()];

    }

    public static function classTreeInfoCacheWrite(): array
    {
        if (self::$aRegenerated && !self::$bclassTreeInfoCacheWriteDone) {
            //TRY TO RECOVER FROM MAX ONE FATAL ERROR!
            register_shutdown_function(function () {
                if(AfrClassDependency::getDebugFatalError()){
                    echo PHP_EOL.'Corrupted classes: '.
                        implode('; ',array_keys(AfrClassDependency::getDebugFatalError())).
                    PHP_EOL;
                }
                AfrMultiClassMapper::classTreeInfoCacheWrite();
            });
            foreach (self::$aNsCl as $sNsCl => $sClassPath) {
                AfrClassDependency::getClassInfo($sNsCl);
            }
            $classRawMap = self::classInterfaceToConcreteMap();
            AfrFileOverWriter::overWrite(
                self::getInterfaceToConcretePath(),
                '<?php return ' . (new AfrArrExportArrayAsStringClass())->exportPhpArrayAsString(
                //AfrClassTree::getTreeInfo()
                    $classRawMap
                ),
                2500,
                4
            );
            self::$bclassTreeInfoCacheWriteDone = true;
            return $classRawMap;
        }
        if (!is_file(self::getInterfaceToConcretePath())) {
            throw new AfrException('AfrClassTree is not resolvable!');
        }
        return include(self::getInterfaceToConcretePath());
    }

    private static function classInterfaceToConcreteMap(): array
    {
        $aOut = [];
        foreach (AfrClassDependency::getDependencyInfo() as $sFQCN => $oEntity) {
            if ($oEntity->isClass()) {
                foreach ($oEntity->getAllDependencies() as $sFQCN_Implementation => $v) {
                    $oDependency = AfrClassDependency::getClassInfo($sFQCN_Implementation);
                    if($oDependency->isInterface()){
                        $aOut[$sFQCN_Implementation][$sFQCN] = $oEntity->isInstantiable();
                    }

                }
            }
        }
        //resolve instantiable classes regardless of dependencies
        foreach (AfrClassDependency::getDependencyInfo() as $sFQCN => $oEntity) {
            if ($oEntity->isClass()) {
                if (empty($aOut[$sFQCN])) {
                    $aOut[$sFQCN] = $oEntity->isInstantiable();
                }
            }
        }

        ksort($aOut);
        return $aOut;
    }

    public static function getNsClassFilesMap(string $sPath): array
    {
        if (!isset(self::$oAfrMultiClassPaths->getPaths()[$sPath])) {
            throw new AfrException('First set the path using ' . __CLASS__ . '::setPaths(AfrMultiClassPaths)');
        }
        self::initPathCacheDir($sPath);
        $sNsClassFilesMapPath = self::getNsClassFilesMapPath($sPath);

        if (self::checkForRegeneration($sPath)) {
            //        echo "LTE: $sPath\n";
            $aClasses = self::buildNewNsClassFilesMap($sPath);
        } else {
            //        echo "$sPath\n";
            $aClasses = (array)(include $sNsClassFilesMapPath);
        }
        //    print_r($aClasses);
        return $aClasses;
    }

    public static function getNsCalssAllPathsMap()
    {
        if (empty(self::$oAfrMultiClassPaths)) {
            throw new AfrException('Run first ' . __CLASS__ . '::setPaths(AfrMultiClassPaths)');
        }
        if (!empty(self::$aNsCl)) {
            return self::$aNsCl;
        }
        foreach (self::$oAfrMultiClassPaths->getPaths() as $sPath => $sDirHash) {
            self::$aNsCl = array_merge(self::$aNsCl, self::getNsClassFilesMap($sPath));
        }
        return self::$aNsCl;
    }

    private static function checkForRegeneration(string $sPath): bool
    {
        if (self::xetRegenerateAll()) {
            return true;
        }

        $sNsClassFilesMapPath = self::getNsClassFilesMapPath($sPath);
        //FIRST RUN:
        if (!is_file($sNsClassFilesMapPath)) {
            return true;
        }
        //VENDOR
        if (self::getVendorPath() === $sPath) {
            return self::getComposerTs() > filemtime($sNsClassFilesMapPath);
        }

        //TODO: pr restul de dirs hash? dar cum fara sa le iterez

        //LOCAL DIR
        if (filemtime($sNsClassFilesMapPath) < time() - 300) {
            return true;
        }
        return false;


    }

}