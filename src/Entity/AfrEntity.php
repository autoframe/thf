<?php
declare(strict_types=1);

namespace Autoframe\Core\Entity;

// https://www.php.net/manual/en/language.oop5.magic.php
use ReflectionClass;
use ReflectionProperty;

class AfrEntity
{
    protected bool $__autoCastByPrefixNotation = true;
    private bool $__dirty;
    private array $__dirtyProperty;

    private static array $__objProps = [];

    public string $sTest = 'Yha!';
    public array $aData;
    private int $iNbr = 88;
    public $mMix;
    public int $iIntConv;

    /**
     * @param array $aAttributes
     */
    public function __construct(array $aAttributes = [])
    {
        if ($aAttributes) {
            //create with array attributes
            foreach ($this->getPublicPropertiesMap() as $sProperty) {
                if (isset($aAttributes[$sProperty])) {
                    $this->castProperty($sProperty, $aAttributes[$sProperty]);
                }
            }
        } elseif (!isset(self::$__objProps[get_class($this)])) {
            //init reflection
            $this->getEntityPublicProperties();
        }
        $this->__dirty = false;
        $this->__dirtyProperty = [];
    }

    /**
     * @return array
     */
    public function getPublicPropertiesMap(): array
    {
        return array_keys($this->getEntityPublicProperties());
    }

    /**
     * Get once the class type and statically store it, because the type can't change while executing the script
     * @return ReflectionProperty[]
     */
    private function getEntityPublicProperties(): array
    {
        $sObjClass = get_class($this);
        if (!isset(self::$__objProps[$sObjClass])) {
            $aProps = [];
            // https://stackoverflow.com/questions/4713680/php-get-and-set-magic-methods
            /** @var \ReflectionProperty $oProp */
            foreach ((new ReflectionClass($this))->getProperties() as $oProp) {
                $sPropName = $oProp->getName();
                if (substr($sPropName, 0, 1) !== '_' && $oProp->isPublic()) {
                    $aProps[$sPropName] = $oProp;
                    //$oReflectionType = $oProp->getType();
                    //$aProps[$sPropName] = $oReflectionType ? $oReflectionType->getName() : null; '*uninitialized*';
                }
                $oProp->setAccessible(false);
            }
            self::$__objProps[$sObjClass] = $aProps;
        }
        return self::$__objProps[$sObjClass];
    }

    /**
     * @return array
     */
    protected function getEntityPublicVars(): array
    {
        $aEntityPublicProperties = $this->getEntityPublicProperties();
        $aVars = array_fill_keys(array_keys($aEntityPublicProperties), null);
        foreach (get_object_vars($this) as $sProp => $mVal) {
            if (isset($aEntityPublicProperties[$sProp])) {
                $aVars[$sProp] = $mVal;
            }
        }
        return $aVars;
    }

    /**
     * Set object properties from an associative array.
     * Ex: $aProperty = [
     *         'member_name_1' => 'value',
     *         'member_name_2' => array( 'value1', 'value2' )
     *     ];
     * @param array $aProperty
     * @return int the number of matched properties
     */
    public function setAssoc(array $aProperty): int
    {
        $i = 0;
        foreach ($aProperty as $sProperty => $mValue) {
            $i += $this->set($sProperty, $mValue) ? 1 : 0;
        }
        return $i;
    }

    /**
     * @param string $sProperty
     * @return bool
     */
    public function __isset(string $sProperty)
    {
        return isset($this->$sProperty);
    }

    /**
     * @param string $sProperty
     * @param $mValue
     * @return void
     */
    public function __set(string $sProperty, $mValue)
    {
        echo "!!!!!!!!!YHA __SET( $sProperty );\n";
        $this->set($sProperty, $mValue);
    }

    /**
     * @param string $sProperty
     * @param $mValue
     * @return bool
     */
    public function set(string $sProperty, $mValue): bool
    {
        if (substr($sProperty, 0, 1) === '_') {
            return false;
        }
        //TODO set peste permis la orice inafara de _Var
        if (true || isset($this->$sProperty) || property_exists($this, $sProperty)) {
            $this->castProperty($sProperty, $mValue);
            return true;
        }
        return false;

    }

    public function __get($name)
    {
        if (isset($this->$name) || property_exists($this, $name)) {
            return $this->$name;
        }
        return null;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return serialize($this);
    }


    /**
     * Get the value for a default member of current class.
     *
     * @param string $sProperty
     * @return mixed  Valoarea proprietatii cerute, daca exista. Null daca nu exista.
     */
    public function get(string $sProperty)
    {
        if (isset($this->$sProperty) || property_exists($this, $sProperty)) {
            return $this->$sProperty;
        }
        return null;
    }

    /**
     * Get a property by reference.
     * @param string $sProperty
     * @return mixed
     */
    public function getReferenced(string $sProperty)
    {
        if (isset($this->$sProperty) || property_exists($this, $sProperty)) {
            $mData = &$this->$sProperty;
            return $mData;
        }
        return null;
    }

    /**
     * @return bool
     */
    public function isDirty(): bool
    {
        return $this->__dirty;
    }

    /**
     * @return void
     */
    public function notDirty(): void
    {
        $this->__dirty = false;
        $this->__dirtyProperty = [];
    }

    /**
     * It will be used when loading/creating a new entity from a database string record
     * @param string $sProperty
     * @param $mValue
     * @return void
     */
    private function castProperty(string $sProperty, $mValue): void
    {
        if (substr($sProperty, 0, 1) == '_') {
            return;
        }
        if ($this->__autoCastByPrefixNotation) {
            $mValue = $this->castToDataType($sProperty, $mValue);
        }
        if (!isset($this->$sProperty) || $this->$sProperty !== $mValue) {
            $this->$sProperty = $mValue;
            $this->__dirty = true;
            $this->__dirtyProperty[$sProperty] = true;
        }


    }

    /**
     * @param string $sProperty
     * @param $mValue
     * @return array|bool|float|int|string|null|object|resource|mixed
     */
    public function castToDataType(string $sProperty, $mValue)
    {
        $sDataType = substr($sProperty, 0, 1);
        if ($sDataType === 'i') { //integer
            if (PHP_INT_SIZE === 4) { // PHP_INT_SIZE will return 4 for x86 version
                $dProcess = (double)$mValue;
                $mValue = ($dProcess < -1 - PHP_INT_MAX || $dProcess > PHP_INT_MAX) ? $dProcess : (int)$mValue;
            } else {//PHP_INT_SIZE will return 8 for x64 version
                $mValue = (int)$mValue;
            }
        } elseif ($sDataType === 'd') {//double
            $mValue = (double)$mValue;
        } elseif ($sDataType === 'f') {//float
            $mValue = (float)$mValue;
        } elseif ($sDataType === 'b') { //boolean
            $mValue = (
                $mValue === '1' ||
                $mValue === 1 ||
                $mValue === 'yes' ||
                $mValue === 'Yes' ||
                !empty($mValue)
            );
        } elseif ($sDataType === 't') { //Y-m-d H:i:s
            $mValue = (string)$mValue;
        } elseif ($sDataType === 's') { //string
            $mValue = (string)$mValue;
        } elseif ($sDataType === 'a') { //array
            $mValue = (array)$mValue;
        } elseif ($sDataType === 'o') { //object
            $mValue = (object)$mValue;
            /*$oValue = new \stdClass();
            if (!is_array($mValue)) {
                $mValue = [$sProperty => $mValue];
            }
            foreach($mValue as $sP=>$mV){
                $sP = (string)$sP;
                $oValue->$sP = $mV;
            }
            $mValue = $oValue;*/
        } elseif ($sDataType === 'r') { //resource | resource (closed) as of PHP 7.2.0
        } elseif ($sDataType === 'm') { //mixed
        } elseif ($sDataType === 'n') { //NULL
            $mValue = null;
        }
        return $mValue;
    }


    /**
     * @param string $sProperty
     * @return array|false|float|int|object|string|null
     */
    public function getDefaultValue(string $sProperty)
    {
        $sDataType = substr($sProperty, 0, 1);
        $mValue = null;
        if ($sDataType === 'i') {
            $mValue = 0;
        } elseif ($sDataType === 'd') {//double
            $mValue = 0.0;
        } elseif ($sDataType === 'f') {//float
            $mValue = 0.0;
        } elseif ($sDataType === 'b') { //boolean
            $mValue = false;
        } elseif ($sDataType === 't') { //Y-m-d H:i:s
            $sDataSubType = substr($sProperty, 0, 2);
            if ($sDataSubType === 'th') {
                $mValue = '00:00:00';
            } elseif ($sDataSubType === 'ty') {
                $mValue = '0000-00-00';
            } else {
                $mValue = '0000-00-00 00:00:00';
            }
        } elseif ($sDataType === 's') { //string
            $mValue = '';
        } elseif ($sDataType === 'a') { //array
            $mValue = [];
        } elseif ($sDataType === 'o') { //object
            $mValue = (object)null;
        } elseif ($sDataType === 'r') { //resource | resource (closed) as of PHP 7.2.0
        } elseif ($sDataType === 'm') { //mixed
        } elseif ($sDataType === 'n') { //NULL
        }
        return $mValue;
    }


    public function resetDefaults()
    {
        var_dump($this->getPublicPropertiesMap());
        echo "-----\n";
        var_dump($this);
        //echo "-----\n".serialize($this);
        echo "-----\n";
        var_dump($this->getEntityPublicVars());
        echo "-----\n";
        var_dump($this->getEntityPublicProperties());
        die;
        if (isset($this->$name) || property_exists($this, $name)) {
            return $this->$name;
        }
        return null;
        $mDefault = $this->getDefaultValue();


        $bIsDefault = false;

        $sDataType = substr(str_replace('_', '', $sProperty), 0, 1);
        $mValue = $oEntity->$sProperty;
        switch ($sDataType) {
            case 'i' :
            case 'd' :
            case 'f' :
                if ($mValue == 0 || $mValue == '0') {
                    $bIsDefault = true;
                }
                break;
            case 't' :
                if ($mValue == '0000-00-00 00:00:00' || $mValue == '0000-00-00' || $mValue === '') {
                    $bIsDefault = true;
                }
                break;
            case 's' :
                if ($mValue == '') {
                    $bIsDefault = true;
                }
                break;
            case 'o' :
                if (is_null($mValue)) {
                    $bIsDefault = true;
                }
                break;
            case 'a' :
                if (is_array($mValue) && count($mValue) == 0) {
                    $bIsDefault = true;
                }
                break;
            case 'r' : //RESOURCE
            case 'm' : //MIEXED
            case 'b' : // la booleeni nu putem determina daca e default sau nu :-)
            default  :
                break;
        }

        return ($bIsDefault);
    } // END func isDefaultValue()


    /**
     * Preia o lista de membri ai altei entitati si isi seteaza acei membri cu valorile asociate
     *
     * @param AfrEntityModel $oObiectSursa
     * @param array $aProprietati
     */
    public function copiazaProprietati(object $oObiectSursa = null): bool
    {
        if (is_null($oObiectSursa)) {
            return false;
        }
        $aClassMembers = array_keys(get_class_vars(get_class($oObiectSursa)));
        $aSelfMembers = array_keys(get_class_vars(get_class($this)));
        if (!count($aClassMembers) || !count($aSelfMembers)) {
            return false;
        }
        $i = 0;
        foreach ($aClassMembers as $sMembru) {
            if (in_array($sMembru, $aSelfMembers)) {
                $this->$sMembru = $oObiectSursa->$sMembru;
                $i++;
            }
        }
        return (bool)$i;
    }


}