<?php
declare(strict_types=1);

namespace Autoframe\Core\Config;

/**
 * Nice config using namespace + ClassName or InterfaceName or TraitName
 * The string argument from the constructor is used as an array key for identification
 * Can also store some array extra data by using assignData()
 */
final class AfrConfig
{
    private string $sNamespaceAndClassOrTraitOrInterfaceOrKey;
    private array $aConstructorArgs = [];
    private array $aMethods = [];
    private array $aStaticMethods = [];
    private array $aProperties = [];
    private array $aStaticProperties = [];
    private array $aData = [];

    /**
     * Nice config using namespace + ClassName or InterfaceName or TraitName
     * The string argument from the constructor is used as an array key for identification
     * Can also store some array extra data by using assignData()
     * @param string $sNamespaceAndClassOrTraitOrInterfaceOrKey
     * @param array $aData
     */
    public function __construct(string $sNamespaceAndClassOrTraitOrInterfaceOrKey, array $aData = [])
    {
        $this->sNamespaceAndClassOrTraitOrInterfaceOrKey = $sNamespaceAndClassOrTraitOrInterfaceOrKey;
        if (!empty($aData)) {
            $this->assignData($aData);
        }
    }

    /**
     * @param array $aData
     * @return $this
     */
    public function assignData(array $aData): AfrConfig
    {
        $this->aData = $aData;
        $this->saveConfig();
        return $this;
    }

    /**
     * @param array $aArgs
     * @return $this
     */
    public function assignConstructorArgs(array $aArgs): AfrConfig
    {
        $this->aConstructorArgs[] = $aArgs;
        $this->saveConfig();
        return $this;
    }

    /**
     * @param array $aProperties
     * @return $this
     */
    public function assignProperties(array $aProperties): AfrConfig
    {
        $this->aProperties = array_merge($this->aProperties, $this->sanitizeProperties($aProperties));
        $this->saveConfig();
        return $this;
    }

    /**
     * @param array $aProperties
     * @return $this
     */
    public function assignStaticProperties(array $aProperties): AfrConfig
    {
        $this->aStaticProperties = array_merge($this->aStaticProperties, $this->sanitizeProperties($aProperties));
        $this->saveConfig();
        return $this;
    }


    /**
     * @param string $sMethodName
     * @param array $aArgs
     * @return $this
     */
    public function assignMethod(string $sMethodName, array $aArgs = []): AfrConfig
    {
        $this->aMethods[] = [$sMethodName, $aArgs];
        $this->saveConfig();
        return $this;
    }

    /**
     * @param string $sMethodName
     * @param array $aArgs
     * @return $this
     */
    public function assignStaticMethod(string $sMethodName, array $aArgs = []): AfrConfig
    {
        $this->aStaticMethods[] = [$sMethodName, $aArgs];
        $this->saveConfig();
        return $this;
    }

    /**
     * @return string
     */
    public function getNamespaceAndClassOrTraitOrInterfaceOrKey(): string
    {
        return $this->sNamespaceAndClassOrTraitOrInterfaceOrKey;
    }

    /**
     * @return array
     */
    public function getConstructorArgs(): array
    {
        return $this->aConstructorArgs;
    }

    /**
     * @return array
     */
    public function getMethods(): array
    {
        return $this->aMethods;
    }

    /**
     * @return array
     */
    public function getStaticMethods(): array
    {
        return $this->aStaticMethods;
    }

    /**
     * @return array
     */
    public function getProperties(): array
    {
        return $this->aProperties;
    }

    /**
     * @return array
     */
    public function getStaticProperties(): array
    {
        return $this->aStaticProperties;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->aData;
    }

    /**
     * @return bool
     */
    private function saveConfig(): bool
    {
        $bStatus = AfrConfigRegister::getInstance()->registerConfig($this);
        if (!$bStatus) {
            echo 'Error to apply config to ' . $this->getNamespaceAndClassOrTraitOrInterfaceOrKey() . PHP_EOL;
            //TODO adauga log eroare aici pe live
        }
        return $bStatus;
    }

    /**
     * @param array $aProperties
     * @return array
     */
    private function sanitizeProperties(array $aProperties): array
    {
        //exclude integer keys
        foreach ($aProperties as $key => $val) {
            if (is_numeric($key)) {
                unset($aProperties[$key]);
            }
        }
        return $aProperties;
    }
}