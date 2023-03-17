<?php
declare(strict_types=1);

namespace Autoframe\Core\Config;

trait AfrConfigurableInstanceTrait
{
    private bool $bAfrConfigurableInstanceExecuted = false;
    private int $iAfrConfigurableInstanceComponents = 0;

    /**
     * @param bool $bForce
     * @return int
     */
    public function applyAfrInstanceConfig(bool $bForce = false): int
    {
        if ($this->bAfrConfigurableInstanceExecuted && !$bForce) {
            return $this->iAfrConfigurableInstanceComponents;
        }

        $aConfigs = AfrConfigRegister::getInstance()->getConfig($this);

        //TODO de verificat daca am si config static si sa il aplic primul daca trebuie


        $iFoundInstanceComponents = 0;
        foreach ($aConfigs as $oConfig) {
            $aProps = $oConfig->getProperties();
            if (!empty($aProps)) {
                $iFoundInstanceComponents++;
                foreach ($aProps as $sProperty => $mValue) {
                    $this->$sProperty = $mValue;
                }
            }

            //todo: sparge in metode private individuale + pentru static la fel
            $aMethods = $oConfig->getMethods();
            if (!empty($aMethods)) {
                $iFoundInstanceComponents++;
                foreach ($aMethods as $aMethodAndArgs) {
                    call_user_func_array([$this, $aMethodAndArgs[0]], $aMethodAndArgs[1]);
                    // [$this, $sMethod]($aArgs); //TODO TEST
                    /*if(empty($aArgs)){
                        $this->{$sMethod}();
                    }
                    else{

                    }*/

                }
            }
        }
        $this->iAfrConfigurableInstanceComponents = $iFoundInstanceComponents;
        $this->bAfrConfigurableInstanceExecuted = true;

        return $this->iAfrConfigurableInstanceComponents;
    }

}