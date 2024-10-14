<?php
declare(strict_types=1);

namespace Autoframe\Core\AW;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Autoframe\Components\Arr\Export\AfrArrExportArrayAsStringClass;

class ListAllComposerClasses
{
/*
    function getComposerAutoloadedClasses(): array
    {
        $classes = [];
        $sStaticMapFile = 'vendor/composer/autoload_classmap.php';
        $installedFile = 'vendor/composer/installed.json'; // Adjust the installed.json file path according to your project


        if (file_exists($sStaticMapFile)) {
            $classes = (include $sStaticMapFile);
            if(!is_array($classes)){
                $classes = [];
            }
        }
        if(empty($baseDir)){
            $baseDir = realpath(dirname($sStaticMapFile).'../../../');
        }


        if (file_exists($installedFile)) {
            $installedData = file_get_contents($installedFile);
            $installedPackages = json_decode($installedData, true);

            foreach ($installedPackages['packages'] as $package) {
                if (isset($package['autoload']['psr-4'])) {
                    foreach ($package['autoload']['psr-4'] as $namespace => $dir) {
                        $dir = is_array($dir) ? array_pop($dir) : $dir;
                        $sPackageDir = 'vendor/' . $package['name'] . '/' . $dir;
                        $iLenPackageDir = strlen($sPackageDir);
                        $iterator = new RecursiveIteratorIterator(
                            new RecursiveDirectoryIterator(
                                $sPackageDir
                            )
                        );
                        foreach ($iterator as $file) {
                            if ($file->isFile() && $file->getExtension() === 'php') {
                                $sFilePath = $file->getPathname();

                                $className = $namespace . strtr(substr($sFilePath, $iLenPackageDir, -4), '/', '\\');
                                $className = str_replace('\\\\', '\\', $className);

                                $classes[$className] = $baseDir.DIRECTORY_SEPARATOR.$sFilePath;
                            }
                        }
                    }
                }
            }
        }
        $oExArr = new AfrArrExportArrayAsStringClass();
        file_put_contents(
            __DIR__.'/cache.php',
            '<?php return '.$oExArr->exportPhpArrayAsString($classes)
        );
        print_r($classes);
        return $classes;
    }*/
}