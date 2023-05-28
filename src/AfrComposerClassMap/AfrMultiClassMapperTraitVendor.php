<?php
declare(strict_types=1);

namespace Autoframe\Core\AfrComposerClassMap;

use Composer\ClassMapGenerator\ClassMapGenerator;

trait AfrMultiClassMapperTraitVendor //todo: de miscat in AfrGetVendorPath si rename accordingly
{

    /**
     * @return int
     */
    private static function getComposerTs(): int
    {
        //pcntl_signal();        //shmop_open();
        $sFile = self::getVendorPath() . self::$sInstalledJson;
        return is_file($sFile) ? (int)filemtime($sFile) : 0;
    }

    protected static string $sInstalledJson = '/composer/installed.json';

    private static function buildNewNsClassFilesMapVendor(): array
    {
        $aClasses = [];
        $aClasses = array_merge($aClasses, self::getComposerAutoloadClassmap());
        $aClasses = array_merge($aClasses, self::getComposerAutoloadPsrx(4));
        $aClasses = array_merge($aClasses, self::getComposerAutoloadPsrx(0));

        $sDsReplace = DIRECTORY_SEPARATOR === '/' ? '\\' : '/';
        foreach ($aClasses as &$sClPath) {
            $sClPath = strtr($sClPath, $sDsReplace, DIRECTORY_SEPARATOR);
        }

        return $aClasses;
    }


    /**
     * @return array
     */
    private static function getComposerAutoloadClassmap(): array
    {
        $aClasses = self::getIncludedPhpArr('autoload_classmap');
        foreach ($aClasses as $sFQCN => &$sClPath) {
            if (!is_file($sClPath)) {
                unset($aClasses[$sFQCN]);
            }
        }
        return $aClasses;
    }


    /**
     * @param int $iPsr
     * @return array
     */
    private static function getComposerAutoloadPsrx(int $iPsr): array
    {
        $aClasses = $aNsDirs = [];

        if ($iPsr === 4) {
            $aNsDirs = self::getIncludedPhpArr('autoload_psr4');
        } elseif ($iPsr === 0) {
            $aNsDirs = self::getIncludedPhpArr('autoload_namespaces');
        }

        foreach ($aNsDirs as $aDirs) {
            foreach ($aDirs as $sPackageDir) {
                if (!is_dir($sPackageDir)) {
                    continue;
                }
                $aClasses = array_merge(ClassMapGenerator::createMap($sPackageDir), $aClasses);
            }
        }
        return $aClasses;
    }


    /**
     * @param $sPhp
     * @return array
     */
    private static function getIncludedPhpArr($sPhp): array
    {
        $aClasses = [];
        $sStaticMapFile = self::getVendorPath() . '/composer/' . $sPhp . '.php';
        if (file_exists($sStaticMapFile)) {
            $aClasses = (include $sStaticMapFile);
            if (!is_array($aClasses)) {
                $aClasses = [];
            }
        }
        unset($aClasses['Autoframe\\Core\\']); //TODO: remove this!
        return $aClasses;
    }

    /**
     * @return string
     */
    private static function getVendorPath(): string
    {
        return AfrGetVendorPath::getVendorPath();
    }


}