<?php
declare(strict_types=1);

namespace Autoframe\Core\AfrComposerClassMap;

use Autoframe\Components\Arr\Export\AfrArrExportArrayAsStringClass;
use Composer\ClassMapGenerator\ClassMapGenerator;

trait AfrMultiClassMapperTraitBase
{

    protected static int $iPermissions = 0755;

    private static function initPathCacheDir(string $sPath): bool
    {
        $sCurrentCachePath = self::getMapDirCachePath($sPath);
        if (!is_dir($sCurrentCachePath)) {
            return mkdir($sCurrentCachePath, self::$iPermissions);
        }
        return true;
    }

    private static function getNsClassFilesMapPath(string $sPath): string
    {
        return self::getMapDirCachePath($sPath) . DIRECTORY_SEPARATOR . self::$sNsClassFilesMap;
    }

    private static function getInterfaceToConcretePath(): string
    {
        return self::xetCacheDir() . DIRECTORY_SEPARATOR . self::$sInterfaceToConcrete;
    }

    private static function getMapDirCachePath($sPath): string
    {
        return self::xetCacheDir() . DIRECTORY_SEPARATOR . self::$oAfrMultiClassPaths->getPaths()[$sPath];
    }

    private static function buildNewNsClassFilesMap(string $sPath): array
    {
        if (self::getVendorPath() === $sPath) {
            $aClasses = self::buildNewNsClassFilesMapVendor();
        } else {
            $aClasses = ClassMapGenerator::createMap($sPath);
        }
        foreach ($aClasses as $sNsCl => &$sClassPath) {
            $sClassPath = (string)(@filemtime($sClassPath)) . '|' . $sClassPath;
        }

        $sNsClassFilesMapPath = self::getNsClassFilesMapPath($sPath);

        self::$aRegenerated[$sPath] = AfrFileOverWriter::overWrite(
            $sNsClassFilesMapPath,
            '<?php return ' . (new AfrArrExportArrayAsStringClass())->exportPhpArrayAsString($aClasses),
            2500,
            4
        );

        return $aClasses;
    }


}