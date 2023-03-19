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
    private array $aConstants = [];
    protected bool $bConstantsDeployed = false;
    private array $aMethods = [];
    private array $aStaticMethods = [];
    private array $aProperties = [];
    private array $aStaticProperties = [];
    private array $aData = [];
    private bool $bPreventExistenceErrors = false;

    /**
     * Nice config using namespace + ClassName or InterfaceName or TraitName
     * The string argument from the constructor is used as an array key for identification
     * Can also store some array extra data by using assignData()
     * @param string $sNamespaceAndClassOrTraitOrInterfaceOrKey
     * @param array $aData
     * @param bool $bMergeWithExistingDataFromAfrConfigRegister
     */
    public function __construct(
        string $sNamespaceAndClassOrTraitOrInterfaceOrKey,
        array  $aData = [],
        bool   $bMergeWithExistingDataFromAfrConfigRegister = true
    )
    {
        $this->sNamespaceAndClassOrTraitOrInterfaceOrKey = $sNamespaceAndClassOrTraitOrInterfaceOrKey;
        // Magic, if class is already registered, then do not overwrite existing data and load it into the current config
        if ($bMergeWithExistingDataFromAfrConfigRegister) {
            $oExisting = AfrConfigRegister::getInstance()->getConfigByKey($this->sNamespaceAndClassOrTraitOrInterfaceOrKey);
            if ($oExisting instanceof AfrConfig) {
                $this->aConstructorArgs = $oExisting->getConstructorArgs();
                $this->aConstants = [['', $oExisting->getConstants()]];
                $this->aMethods = $oExisting->getMethods();
                $this->aStaticMethods = $oExisting->getStaticMethods();
                $this->aProperties = $oExisting->getProperties();
                $this->aStaticProperties = $oExisting->getStaticProperties();
                $this->aData = $oExisting->getData();
                $this->bPreventExistenceErrors = $oExisting->getPreventExistenceErrors();
                $this->bConstantsDeployed = $oExisting->bConstantsDeployed;
            }
        }
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
        $this->aData = array_merge($this->aData, $aData);
        $this->saveConfig();
        return $this;
    }

    /**
     * Assign KEY => value array
     * Namespace is optional
     * @param array $aConstants
     * @param string $sNamespace
     * @return $this
     */
    public function assignConstants(array $aConstants, string $sNamespace = '\\'): AfrConfig
    {
        $this->aConstants[] = [$sNamespace, $this->sanitizeProperties($aConstants)];
        $this->bConstantsDeployed = false;
        $this->saveConfig();
        return $this;
    }

    /**
     * @return AfrConfig
     */
    public function defineConstants(): AfrConfig
    {
        if (!$this->bConstantsDeployed) {
            foreach ($this->getConstants() as $sNsConstant => $mValue) {
                if (!defined($sNsConstant)) {
                    define($sNsConstant, $mValue);
                }
            }
            $this->bConstantsDeployed = true;
            $this->saveConfig();
        }
        return $this;
    }

    /**
     * @return array
     */
    public function getConstants(): array
    {
        $aOut = [];
        foreach ($this->aConstants as $aConstantNsBlock) {
            $sNamespace = $aConstantNsBlock[0];
            foreach ($aConstantNsBlock[1] as $sConstantName => $mValue) {
                $sNsConstant = trim($sNamespace . $sConstantName, '\ ');
                if (!isset($aOut[$sNsConstant])) {
                    $aOut[$sNsConstant] = defined($sNsConstant) ? constant($sNsConstant) : $mValue;
                }
            }
        }
        return $aOut;
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
     * @param bool $bPreventExistenceErrors
     * @return $this
     */
    public function assignPreventExistenceErrors(bool $bPreventExistenceErrors): AfrConfig
    {
        $this->bPreventExistenceErrors = $bPreventExistenceErrors;
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
    public function getPreventExistenceErrors(): bool
    {
        return $this->bPreventExistenceErrors;
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