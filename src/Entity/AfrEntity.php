<?php
declare(strict_types=1);

namespace Autoframe\Core\Entity;

use Autoframe\Core\Entity;

// https://www.php.net/manual/en/language.oop5.magic.php
class AfrEntity
{
    /**
     * Sirul de mapari folosit la compararea atributelor.
     * Cand avem nevoie de ea, o generam f. usor folosind
     *        `$this->generateMapping();`
     *
     * @var array
     */
    var $aMap = array();
    /**
     * Atributele ce nu sunt luate in considerare la
     * rularea metodei `$this->differencesWith()`.
     *
     * @var array
     */
    var $aIgnoredAttributes = array();

    /**
     * Colectiile de date pentru modul editare/vizualizare
     */
    var $aCollections = array();

    public function __construct(array $attributes=[])
    {
        if($attributes){
            foreach (get_object_vars($this) as $key => $value) {
                $this->$key = $attributes[$key] ?? null;
            }
        }

    }

    /**
     * Castuim datele ca sa se potriveasca pe membri
     *
     * @param string $sProperty
     * @param bool $bCast
     * @param unknown_type $mValue
     */
    function cast($sProperty, $mValue, $bCast = true)
    {
        $this->__set();
        $sDataType = substr(str_replace('_', '', $sProperty), 0, 1);
        switch ($sDataType) {
            case 'i':
                $this->castInt($sProperty, $mValue, $bCast);
                break;
            case 'd':
            case 'f':
                $this->castFloat($sProperty, $mValue);
                break;
            case 'b':
                $this->castBool($sProperty, $mValue);
                break;
            case 't':
//		    	    $this->$sProperty = strtotime( $mValue ); break;
            case 's': //STR
            case 'o': //ONJ
            case 'a': //ARR
            case 'r': //RESOURCE
            case 'm': //MIXED
            default:
                $this->$sProperty = $mValue;
                break;
        }

    } // END func cast()

    protected function castFloat($sProperty, $mValue)
    {
        if (is_numeric($mValue)) {
            $this->$sProperty = (float)$mValue;
        } else {
            $this->$sProperty = $mValue;
        }
    }

    protected function castInt($sProperty, $mValue, $bCast)
    {
        if (is_numeric($mValue)) {
            $this->$sProperty = strlen($mValue) >= 10 ? (double)$mValue : (int)$mValue;
        } else {
            $this->$sProperty = $bCast ? (int)$mValue : $mValue;
        }
    }

    protected function castBool($sProperty, $mValue)
    {
        if (($mValue === 'yes') || ($mValue === 'no')
            || ($mValue === '0') || ($mValue === '1')
            || ($mValue === 0) || ($mValue === 1)
        ) {
            $this->$sProperty = (boolean)$mValue;
        } else {
            $this->$sProperty = $mValue;
        }
    }

    /**
     * set a value for default properties of this class
     *
     * @param string $sProperty
     * @param mixed $sValue
     * @param bool $bCast executa sau nu cast
     * @return
     *     PEAR error daca nu exista membrul clasei ce se vrea a fi setat
     */
    function set($sProperty, $mValue, $bCast = true)
    {

        $sProperty = $this->convertMemberName($sProperty);
        // DEBUG - uncomment next lines to debug - debugging can be seen in the logs
        /*
	    $sLogMessage = get_class($this) . '::set(' . $sProperty . ', '. gettype($mValue)  .' ) = $$SNIPPET$$';
	    if( gettype($mValue) == 'string' ) {
	       $sLogMessage = str_replace('$$SNIPPET$$', substr($mValue,0,20), $sLogMessage);
	    } elseif( is_numeric($mValue) ) {
	       $sLogMessage = str_replace('$$SNIPPET$$', (string)($mValue), $sLogMessage);
	    } else {
	        $sLogMessage = str_replace('$$SNIPPET$$', '<SMTHNG>', $sLogMessage);
	    }

	    log_message( 'debug', $sLogMessage);
        /*  */

        //if( array_key_exists( $sProperty, get_object_vars( $this ) ) ) {	//wtf? this is stupid!
        if (isset($this->$sProperty) || property_exists($this, $sProperty)) {
            $this->cast($sProperty, $mValue, $bCast);
        } // end if

    } // END func set()

    /**
     * Set object properties from an associative array.
     *
     * @param array $aFields
     * @param bool $bCast executa sau nu cast
     * Ex: $aFields = array(
     *         'member_name_1' => 'value',
     *         'member_name_2' => array( 'value1', 'value2' )
     *     );
     */
    function setAssoc($aFields, $bCast = true)
    {
        if (!is_array($aFields)) {
            return;
        }
        foreach ($aFields as $sProperty => $mValue) {
            $this->set($sProperty, $mValue, $bCast);
        }
    } // END func setAssoc()

    public function attachAssoc(array $aFields)
    {
        foreach ($aFields as $sProperty => $mValue) {
            $this->$sProperty = $mValue;
        }
    }

    /**
     * Get the value for a default member of current class.
     *
     * @param string $sProperty
     * @return mixed  Valoarea proprietatii cerute, daca exista. Null daca nu exista.
     */
    function get($sProperty)
    {
        $mToReturn = NULL;
        $sProperty = $this->convertMemberName($sProperty);
        if (isset($this->$sProperty) || property_exists($this, $sProperty)) {
            //if( array_key_exists( $sProperty, get_object_vars( $this ) ) ) { wtf again?
            return $this->$sProperty;
        }
        return ($mToReturn);
    } // END func get()

    /**
     * Get a member object by reference.
     *
     * @param string $sProperty
     * @return mixed
     */
    function getObject($sProperty)
    {

        if (isset($this->$sProperty) || property_exists($this, $sProperty)) {
            //if( array_key_exists( $sProperty, get_object_vars( $this ) ) ) {
            $oObject = &$this->$sProperty;
            if (is_object($oObject)) {
                return $oObject;
            }
        }
        return null;
    } // END func getObject()


    /**
     * Compara entitatea curenta cu o alta folosint doar cheile din arrayul
     * de mapare (param 2) si returneaza un array de atribute cu continut
     * diferit.
     *
     * Este folosit pentru compararile entitatilor din baza de date cu cele
     * primite pentru modificari etc.
     *
     * @access public
     * @param
     *      object      Entitatea cu care comparam $this.
     *      array       Maparile atribut-*whatever* (optional)
     *      boolean     Depanare
     * @return  array     array de atribute a caror valoare difera
     */
    function differencesWith(&$oEntity2, $aMap = NULL, $bDebug = false, $sPrepend = '')
    {

        $bOk = true;
        $aDifferent = array(); // arrayul de atribute diferite

        if ($aMap == NULL) {
            if (count($this->aMap) <= 0) {
                $this->_generateMapping();
            }
            $aMap = &$this->aMap;
        }
        // este evident de ce :-) vom exclude din comparatie membrii de mai jos:
        array_push($this->aIgnoredAttributes, 'aMap');
        array_push($this->aIgnoredAttributes, 'aIgnoredAttributes');

        if (!$this || !$oEntity2) {
            if ($bDebug) {
                if (!$this) {
                    echo '$this parameter';
                } elseif (!$oEntity2) {
                    echo '$oEntity2 parameter';
                } else {
                    echo 'Some weird parameter';
                }
                echo ' is null (or false or something)!<br />';

            }
            $bOk = false;
        } else {
            // verificam atributele celor doua entitati
            if ($bDebug) {
                echo '<hr />';
            }

            foreach ($aMap as $sKey => $sValue) {

                // verificam daca acest camp este ignorat sau nu
                $bIgnored = true;
                if (isset($this->aIgnoredAttributes) && count($this->aIgnoredAttributes)) {
                    if (!in_array($sKey, $this->aIgnoredAttributes)) {
                        $bIgnored = false;
                    }
                } else {
                    $bIgnored = false;
                }

                // verificam daca elementele sunt diferite (si campul nu-i ignorat)
                if (($this->get($sKey) != $oEntity2->get($sKey)) &&
                    !$bIgnored) {
                    $bOk = false;

                    // afisam care campuri nu pusca?
                    if ($bDebug) {
                        echo "<br />Field $sKey in oEntity1 is '" . $this->get($sKey) .
                            "', should be '" . $oEntity2->get($sKey) . "' <br />";
                    }

                    $aDifferent[] = $sPrepend . $sKey;

                }

                //TODO: cand se vor modifica structurile, entitatile, maparile de campuri,
                // se va putea generaliza functionalitatea de comparare recursiva intr-un mod
                // mai eficient
                // // Verificam daca trebuie comparate eventualele entitati incluse in
                // // entitatile comparate
                // if( is_object($this->get($sKey) || is_object($oEntity2->get($sKey) ) ) ) {
                //      if( is_a($this->get($sKey), 'AfrEntityModel' ) &&
                //          is_a($oEntity2->get($sKey), 'AfrEntityModel') ) {
                //
                //          $oEntInclusa = $this->get($sKey);
                //          $oEntInclusa2 = $oEntity->get($sKey);
                //
                //           $aDiffInclusa = $oEntInclusa->diffWith($oEntInclusa2, );
                //
                //        }
                //  }

            } //...foreach
            if ($bDebug) {
                echo '<hr />';
            }

        }

        return $aDifferent;

    } // END func differencesWith()

    /**
     * Genereaza maparea $this->aMap.
     *
     * @access private
     */
    function _generateMapping()
    {
        $aClassMembers = array_keys(get_class_vars(get_class($this)));

        foreach ($aClassMembers as $sMemberName) {
            $this->aMap[$sMemberName] = $this->get($sMemberName);
        }
    } // END func generateMapping()

    /**
     * Determinam daca valoarea e default sau nu.
     *
     * @param unknown_type $oEntity
     * @param unknown_type $sProperty
     * @return boolean
     */
    function isDefaultValue($oEntity, $sProperty = '')
    {
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
                if ($mValue == '0000-00-00 00:00:00' || $mValue == '0000-00-00') {
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
    public function copiazaProprietati($oObiectSursa = null)
    {
        if (is_null($oObiectSursa)) {
            return false;
        }
        $aClassMembers = array_keys(get_class_vars(get_class($oObiectSursa)));
        $aSelfMembers = array_keys(get_class_vars(get_class($this)));
        if (!count($aClassMembers) || !count($aSelfMembers)) {
            return false;
        }
        foreach ($aClassMembers as $sMembru) {
            if (in_array($sMembru, $aSelfMembers)) {
                $this->$sMembru = $oObiectSursa->$sMembru;
            }
        }
    } // END func copiazaProprietati()

    public function __isset($name)
    {
        $name = $this->convertMemberName($name);
        return isset($this->$name);
    }

    public function __set($name, $value)
    {
        $name = $this->convertMemberName($name);
        $this->$name = $value;
    }

    public function __get($name)
    {
        $name = $this->convertMemberName($name);
        if (isset($this->$name) || property_exists($this, $name)) {
            return $this->$name;
        }
        return null;
    }

    private function convertMemberName($sName)
    {
        if (isset($this->sDynamicInstance)) {
            return str_replace("_" . $this->sDynamicInstance, "", $sName);
        }
        return $sName;
    }

    public function getCurrentMembers()
    {

        if (isset($this->sDynamicInstance)) {
            $aMembers = array();
            foreach (get_object_vars($this) as $sKey => $sValue) {
                $aMembers[] = $sKey . "_" . $this->sDynamicInstance;
            }
            return $aMembers;
        } else {
            return array_keys(get_object_vars($this));
        }

    }

    public function getClass()
    {
        if (isset($this->sDynamicInstance)) {
            return get_class($this) . '_' . $this->sDynamicInstance;
        } else {
            return get_class($this);
        }
    }

    public function getIdFromCollection($sField)
    {
        return isset($this->$sField) && !empty($this->aCollections[$sField])
            ? array_search($this->$sField, $this->aCollections[$sField]) : false;
    }


}