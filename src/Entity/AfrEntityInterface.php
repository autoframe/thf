<?php

namespace Autoframe\Core\Entity;

use Autoframe\Core\Entity\Exception\AfrEntityException;

interface AfrEntityInterface
{
    /**
     * @param $mProperties
     */
    public function __construct($mProperties = []);

    /**
     * @param string $sProperty
     * @return bool
     */
    public function isPublic(string $sProperty): bool;

    /**
     * @return array
     */
    public function getEntityPublicVars(): array;

    /**
     * Set object properties from an associative array.
     * Ex: $aProperty = [
     *         'member_name_1' => 'value',
     *         'member_name_2' => array( 'value1', 'value2' )
     *     ];
     * @param $aProperty
     * @return int the number of matched properties
     */
    public function setAssoc($aProperty): int;

    /**
     * @param string $sProperty
     * @param $mValue
     * @return void
     * @throws AfrEntityException
     */
    public function __set(string $sProperty, $mValue): void;

    /**
     * @param string $sProperty
     * @return bool
     */
    public function __isset(string $sProperty);

    /**
     * @param $name
     * @return mixed
     */
    public function __get($name);

    /**
     * @return string
     */
    public function __toString(): string;

    /**
     * @param string $sProperty
     * @return mixed Entity value if exist or Null
     */
    public function get(string $sProperty);

    /**
     * Get a property by reference.
     * @param string $sProperty
     * @return mixed Entity value if exist or Null
     */
    public function getReferenced(string $sProperty);

    /**
     * @return bool
     */
    public function isDirty(): bool;

    /**
     * @return void
     */
    public function notDirty(): void;

    /**
     * @return array
     */
    public function getDirtyProperties(): array;

    /**
     * It will be used when loading/creating a new entity from a database string record
     * @param string $sProperty
     * @param $mValue
     * @return void
     */
    public function castProperty(string $sProperty, $mValue): void;

    /**
     * @param string $sProperty
     * @param $mValue
     * @return array|bool|float|int|string|null|object|resource|mixed
     */
    public function castToDataType(string $sProperty, $mValue);

    /**
     * @param string $sProperty
     * @return array|false|float|int|object|string|null
     */
    public function getDefaultValue(string $sProperty);

    /**
     * @return int
     */
    public function resetDefaults(): int;

    /**
     * @param object $oSourceObject
     * @return bool
     */
    public function copyPublicProperties(object $oSourceObject): bool;

    /**
     * @param bool $bOnlyDirty
     * @return array
     */
    public function castForDatabase(bool $bOnlyDirty = false): array;
}