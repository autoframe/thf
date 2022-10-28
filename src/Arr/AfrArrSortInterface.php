<?php

namespace Autoframe\Core\Arr;

interface AfrArrSortInterface
{
    /**
     * @param array $aArray
     * @param $mDirectionOrCallableFn ; SORT_ASC|SORT_DESC|callable
     * @param $mSortByKey
     * @param int $iFlags
     * @return bool
     */
    public function arrXSort(array &$aArray, $mDirectionOrCallableFn = SORT_ASC, $mSortByKey = false, int $iFlags = SORT_NATURAL): bool;

    /**
     * @param array $aMultiLevelToSort
     * @param string $sSubArrayKey
     * @param int $iOrder
     * @param int $iFlag
     * @return array
     */
    public function arrSortBySubKey(array $aMultiLevelToSort, string $sSubArrayKey, int $iOrder = SORT_ASC, int $iFlag = SORT_REGULAR): array;
}