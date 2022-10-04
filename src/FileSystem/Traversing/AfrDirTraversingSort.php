<?php

namespace Autoframe\Core\FileSystem\Traversing;

use Autoframe\Core\FileSystem\Traversing\Exception\FileSystemTraversingException;
use function is_string;
use function in_array;
use function is_callable;
use function print_r;
use function call_user_func_array;
use function array_keys;


trait AfrDirTraversingSort
{

    //global static settings that have ahigher prioroty then instance settings
    //to clear them, run: setAfrDirTraversingSortMethod(null,[],true);
    /** @var string|Closure */
    private static $GlobalAfrDirTraversingSortFunction;
    private static array $GlobalAfrDirTraversingSortFunctionArgs = [];

    // instance settings
    /** @var string|Closure */
    private $AfrDirTraversingSortFunction = 'ksort';
    private array $AfrDirTraversingSortFunctionArgs = [SORT_NATURAL];

    /**
     * @param $mFunction
     * @param array $aOptionalArgs
     * @param bool $bGlobal
     * @return void
     * @throws FileSystemTraversingException
     */
    public function setAfrDirTraversingSortMethod($mFunction, array $aOptionalArgs = [], bool $bGlobal = false): void
    {
        if ($bGlobal && empty($mFunction) && empty($aOptionalArgs)) {
            self::$GlobalAfrDirTraversingSortFunction = '';
            self::$GlobalAfrDirTraversingSortFunctionArgs = [];
            return;
        }

        $bValid = false;
        if (is_string($mFunction)) {
            if (in_array(
                $mFunction, [
                    'asort',
                    'arsort',
                    'krsort',
                    'ksort',
                    'natcasesort',
                    'natsort',
                    'rsort',
                    'shuffle',
                    'sort',
                    'array_multisort',
                    'uasort',
                    'uksort',
                    'usort'
                ]
            )) {
                $bValid = true;
            }

        } elseif (is_callable($mFunction)) {
            $bValid = true;
        }

        if ($bValid) {
            if ($bGlobal) {
                self::$GlobalAfrDirTraversingSortFunction = $mFunction;
                self::$GlobalAfrDirTraversingSortFunctionArgs = $aOptionalArgs;
            } else {
                $this->AfrDirTraversingSortFunction = $mFunction;
                $this->AfrDirTraversingSortFunctionArgs = $aOptionalArgs;
            }
        } else {
            throw new FileSystemTraversingException(
                '$mFunction must be callable string|Closure in ' . __FUNCTION__ . '; ' . print_r($mFunction, true)
            );
        }
    }

    /**
     * @param array $arrayToSort
     * @return array|mixed
     * @throws FileSystemTraversingException
     */
    private function applyAfrDirTraversingSortMethod(array &$arrayToSort)
    {
        $aParams = [&$arrayToSort];
        if (self::$GlobalAfrDirTraversingSortFunction && is_callable(self::$GlobalAfrDirTraversingSortFunction)) {
            $fn = self::$GlobalAfrDirTraversingSortFunction;
            foreach (self::$GlobalAfrDirTraversingSortFunctionArgs as $mVal) {
                $aParams[] = $mVal;
            }
        } elseif (is_callable($this->AfrDirTraversingSortFunction)) {
            $fn = $this->AfrDirTraversingSortFunction;
            foreach ($this->AfrDirTraversingSortFunctionArgs as $mVal) {
                $aParams[] = $mVal;
            }
        } else {
            throw new FileSystemTraversingException(
                'Unable to call sort method in: ' . __FUNCTION__ . ' for ' .
                print_r(
                    [self::$GlobalAfrDirTraversingSortFunctionArgs, $this->AfrDirTraversingSortFunctionArgs, $aParams],
                    true
                ),
            );
        }
        return $this->applyAfrDirTraversingSortMethodToKeys($fn, $aParams);
    }

    /**
     * @param $fn
     * @param $aParams
     * @return array|mixed
     * @throws FileSystemTraversingException
     */
    private function applyAfrDirTraversingSortMethodToKeys($fn, &$aParams)
    {
        if (is_string($fn) && in_array($fn, ['krsort', 'ksort', 'uksort'])) {
            return call_user_func_array($fn, $aParams);
        } else {
            //hack to reverse keys
            $aOriginalToSort = $aParams[0];
            $aParams[0] = array_keys($aOriginalToSort); //flip the keys
            $fnReturn = call_user_func_array($fn, $aParams);
            $aNewMultiLevel = [];
            foreach ($aParams[0] as $sNewSortedKey) {
                $aNewMultiLevel[$sNewSortedKey] = $aOriginalToSort[$sNewSortedKey];
            }
            $aParams[0] = $aNewMultiLevel;
            if (is_array($fnReturn)) {
                foreach ($fnReturn as $mVal) {
                    if (!is_string($mVal) || !isset($aNewMultiLevel[$sNewSortedKey])) {
                        //return $aOriginalToSort;
                        throw new FileSystemTraversingException('Unknown custom sort in ' . __FUNCTION__);
                    }
                }
                return $aNewMultiLevel;
            }
            return $fnReturn;
        }
    }
}
