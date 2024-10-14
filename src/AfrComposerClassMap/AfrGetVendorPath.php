<?php
declare(strict_types=1);

namespace Autoframe\Core\AfrComposerClassMap;

use function is_array;
use function spl_autoload_functions;
use function class_exists;
use function explode;
use function realpath;
use function count;
use function substr;
use function in_array;
use function implode;
use function is_file;
use function str_repeat;

/**
 * This will detect the real vendor path:
 * /shared/app/afr/vendor
 * C:\xampp\htdocs\afr\vendor
 * Empty string '' is returned on detection fail
 */
class AfrGetVendorPath
{
    protected static string $sVendor = 'vendor';
    protected static string $sInstalledJson = '/composer/installed.json';
    protected static string $sVendorPath;

    /**
     * This will detect the real vendor path:
     * /shared/app/afr/vendor
     * C:\xampp\htdocs\afr\vendor
     * Empty string '' is returned on detection fail
     * @return string
     */
    public static function getVendorPath(): string
    {
        if (!isset(self::$sVendorPath)) {
            self::$sVendorPath = self::detectVendorPath();
        }
        return self::$sVendorPath;
    }


    /** Detects a valid vendor path in the file system
     * @return string
     */
    private static function detectVendorPath(): string
    {
        $sDs = DIRECTORY_SEPARATOR;
        $sDsUp = '..' . $sDs;
        foreach ([
                     __DIR__, //production, already installed in composer
                     __DIR__ . $sDs . str_repeat($sDsUp, 2) . self::$sVendor, //local dev1
                     __DIR__ . $sDs . str_repeat($sDsUp, 3) . self::$sVendor, //local dev2
                     __DIR__ . $sDs . str_repeat($sDsUp, 4) . self::$sVendor, //local dev3
                     __DIR__ . $sDs . str_repeat($sDsUp, 5) . self::$sVendor, //local dev4
                     __DIR__ . $sDs . str_repeat($sDsUp, 6) . self::$sVendor, //local dev5
                 ] as $sDir) {
            $sVendorPath = self::checkForComposerVendorPath($sDir);
            if ($sVendorPath) {
                return $sVendorPath;
            }
        }

        //fallback: script is loaded outside composer vendor dir or other custom path:
        $i = 0;
        foreach (self::getSplComposerClassMap() as $sDir) {
            if ($i >= 10) {
                break; //limit to 10 entries is a reasonable value before failing
            }
            $i++;
            $sVendorPath = self::checkForComposerVendorPath($sDir);
            if ($sVendorPath) {
                return $sVendorPath;
            }
        }
        return ''; //fail!
    }

    /** Detects if composer is installed into a valid vendor path
     * @param string $sPath
     * @return string
     */
    private static function checkForComposerVendorPath(string $sPath): string
    {
        $arRealPath = explode(self::$sVendor, (string)realpath($sPath));
        $iParts = count($arRealPath);
        if ($iParts < 2) {
            return '';
        }
        $sChrAfterVendor = substr($arRealPath [$iParts - 1], 0, 1);
        $sChrBeforeVendor = substr($arRealPath [$iParts - 2], -1, 1);
        if (
            !in_array($sChrAfterVendor, ['', '\\', '/']) ||
            !in_array($sChrBeforeVendor, ['\\', '/'])
        ) {
            return '';
        }
        $arRealPath [$iParts - 1] = ''; //clear last part
        $sRealPath = implode(self::$sVendor, $arRealPath);
        return is_file($sRealPath . self::$sInstalledJson) ? (string)realpath($sRealPath) : '';
    }

    /**
     * @return array
     */
    private static function getSplComposerClassMap(): array
    {
        $sClassLoader = 'Composer\\Autoload\\ClassLoader';
        if (!class_exists($sClassLoader)) {
            return [];
        }
        foreach ((array)spl_autoload_functions() as $aAutoloadFunction) {
            if (!is_array($aAutoloadFunction)) {
                continue;
            }
            /** @var \Composer\Autoload\ClassLoader $mLoader */
            foreach ($aAutoloadFunction as $mLoader) {
                if ($mLoader instanceof $sClassLoader) {
                    return (array)$mLoader->getClassMap();
                }
            }
        }
        return [];
    }

}