<?php

namespace Autoframe\Core\Arr;

use function array_flip;
use function gettype;
use function krsort;
use function arsort;
use function ksort;
use function asort;
use function is_callable;
use function uksort;
use function uasort;
use function natsort;
use function natcasesort;
use function shuffle;

trait AfrArrSort
{
    /**
     * @param array $aArray
     * @param $mDirectionOrCallableFn ; SORT_ASC|SORT_DESC|callable
     * @param $mSortByKey
     * @param int $iFlags
     * @return bool
     */
    public function arrXSort(
        array &$aArray,
              $mDirectionOrCallableFn = SORT_ASC,
              $mSortByKey = false,
        int   $iFlags = SORT_NATURAL
    ): bool
    {
        //detect array data type...
        if ($mSortByKey === '' || $mSortByKey === null || $mSortByKey === -1) {
            $aSortableTypes = array_flip(['boolean', 'integer', 'double', 'string', 'NULL']);
            $bSortByKey = true;
            foreach ($aArray as $mVal) {
                if (!isset($aSortableTypes[gettype($mVal)])) {
                    $bSortByKey = false;
                    break;
                }
            }
        } else {
            $bSortByKey = (bool)$mSortByKey;
        }
        if ($mDirectionOrCallableFn === SORT_DESC) {
            return $bSortByKey ? krsort($aArray, $iFlags) : arsort($aArray, $iFlags);

        } elseif ($mDirectionOrCallableFn === SORT_ASC) {
            return $bSortByKey ? ksort($aArray, $iFlags) : asort($aArray, $iFlags);
        } elseif (
            $mDirectionOrCallableFn === 'natsort' ||
            $mDirectionOrCallableFn === 'natcasesort' ||
            $mDirectionOrCallableFn === 'shuffle'
        ) {
            return $mDirectionOrCallableFn($aArray);
        } elseif (is_callable($mDirectionOrCallableFn)) {
            return $bSortByKey ? uksort($aArray, $mDirectionOrCallableFn) : uasort($aArray, $mDirectionOrCallableFn);
        }
        return false;

    }


    /**
     * @param array $aMultiLevelToSort
     * @param string $sSubArrayKey
     * @param int $iOrder
     * @param int $iFlag
     * @return array
     */
    public function arrSortBySubKey(
        array  $aMultiLevelToSort,
        string $sSubArrayKey,
        int    $iOrder = SORT_ASC,
        int    $iFlag = SORT_REGULAR
    ): array
    {
        if (!$aMultiLevelToSort) {
            return $aMultiLevelToSort;
        }
        $aNew = $aSortable = [];

        foreach ($aMultiLevelToSort as $k => $v) {
            if (is_array($v) && isset($v[$sSubArrayKey])) {
                $aSortable[$k] = $v[$sSubArrayKey];
            } elseif (is_object($v) && property_exists($v, $sSubArrayKey)) {
                $aSortable[$k] = $v->$sSubArrayKey;
            } else {
                $aSortable[$k] = $v;
            }
        }

        switch ($iOrder) {
            case SORT_ASC:
                asort($aSortable, $iFlag);
                break;
            case SORT_DESC:
                arsort($aSortable, $iFlag);
                break;
        }

        foreach ($aSortable as $k => &$v) {
            $aNew[$k] = $aMultiLevelToSort[$k];
            unset($aMultiLevelToSort[$k], $v);
        }
        return $aNew;
    }


}