<?php
declare(strict_types=1);

namespace Autoframe\Core\AfrComposerClassMap;

use Autoframe\Components\Exception\AfrException;

use function array_merge;
use function realpath;
use function is_dir;
use function print_r;
use function trim;
use function base_convert;
use function md5;

/**
 * This will make a configuration object that contains the paths to be wired:
 *
 * $oAfrConfigMapWiringPaths = new AfrConfigMapWiringPaths(['src','vendor']);
 * AfrMultiClassMapper::setMultiPaths($oAfrConfigMapWiringPaths);
 * AfrMultiClassMapper::xetRegenerateAll(true);
 * register_shutdown_function(function(){ print_r(AfrClassDependency::getDependencyInfo());});
 * $aMaps = AfrMultiClassMapper::doMagic();
 */
class AfrConfigMapWiringPaths
{
    protected array $aPaths = [];

    /**
     * @param array $aPaths
     * @param bool $bForceVendor
     * @throws AfrException
     */
    public function __construct(array $aPaths, bool $bForceVendor = true)
    {
        if ($bForceVendor && $sVendorPath = AfrGetVendorPath::getVendorPath()) {
            $aPaths = array_merge([$sVendorPath], $aPaths);
        }

        foreach ($aPaths as $sPath) {
            $sPath = (string)$sPath;
            $sPath = realpath($sPath);
            if ($sPath === false || !is_dir($sPath)) {
                throw new AfrException(
                    'Invalid paths for ' . __CLASS__ . '->' . __FUNCTION__ . '->' . print_r($aPaths, true)
                );
            }
            $this->aPaths[$sPath] = trim(base_convert(md5($sPath), 16, 32), '0');
        }
    }

    /**
     * @return array
     */
    public function getPaths(): array
    {
        return $this->aPaths;
    }

}