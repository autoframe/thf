<?php

namespace Autoframe\Core\FileSystem\Traversing;

use Autoframe\Core\FileSystem\Traversing\Exception\FileSystemTraversingException;
use Autoframe\Core\Arr\AfrArrSort;

use function count;
use function array_slice;
use function print_r;

/**
 * Global static settings that have a higher priority then instance settings
 * To clear them, run: setAfrDirTraversingSortMethod(null,[],true);
 */
trait AfrDirTraversingSort
{
    use AfrArrSort;

    //TODO: As of PHP 8.1.0, calling a static method, or accessing a static property directly on a trait is deprecated.
    // Static methods and properties should only be accessed on a class using the trait.
    // global settings regardless of instance: ?
    /** @var null|string|Closure|;SORT_ASC|SORT_DESC */
    private static $GlobalAfrDirTraversingSortDirectionOrFunction;
    /** @var null|int bitwhise */
    private static $GlobalAfrDirTraversingSortFlags;

    // instance settings
    /** @var string|Closure|;SORT_ASC|SORT_DESC */
    private $AfrDirTraversingSortDirectionOrFunction = SORT_ASC;
    /** @var int bitwise */
    private $AfrDirTraversingSortFlags = SORT_NATURAL;

    /**
     * @param $mDirectionOrCallableFn
     * @param array $aOptionalArgs
     * @param bool $bGlobal
     * @return void
     * @throws FileSystemTraversingException
     */
    public function setAfrDirTraversingSortMethod(
        bool $bGlobal = false,
             $mDirectionOrCallableFn = SORT_ASC,
        int  $flags = SORT_NATURAL
    ): void
    {
        //global cleanup
        if ($bGlobal && empty($mDirectionOrCallableFn)) {
            self::$GlobalAfrDirTraversingSortDirectionOrFunction =
            self::$GlobalAfrDirTraversingSortFlags = null;
            return;
        }

        //todo: eventual de folosit ca si clasa stand alone care sa contina ArrXSort; constructor in trait?
        $aTest = ['a', 'b'];
        if ($this->arrXSort($aTest, $mDirectionOrCallableFn, false, $flags)) {
            if ($bGlobal) {
                self::$GlobalAfrDirTraversingSortDirectionOrFunction = $mDirectionOrCallableFn;
                self::$GlobalAfrDirTraversingSortFlags = $flags;

            } else {
                $this->AfrDirTraversingSortDirectionOrFunction = $mDirectionOrCallableFn;
                $this->AfrDirTraversingSortFlags = $flags;
            }
        } else {
            throw new FileSystemTraversingException(
                '$mFunction must be callable.string|Closure|SORT_ASC|SORT_DESC in ' .
                __FUNCTION__ . '; ' . print_r($mDirectionOrCallableFn, true)
            );
        }
    }

    /**
     * @param array $arrayToSort
     * @return array|mixed
     * @throws FileSystemTraversingException
     */
    private function applyAfrDirTraversingSortMethod(array &$arrayToSort, bool $bSortByKey)
    {
        $aTest = ['a', 'b'];
        if (self::$GlobalAfrDirTraversingSortDirectionOrFunction) {
            return $this->arrXSort(
                $arrayToSort,
                self::$GlobalAfrDirTraversingSortDirectionOrFunction,
                $bSortByKey,
                self::$GlobalAfrDirTraversingSortFlags,
            );
        } elseif (
            $this->AfrDirTraversingSortDirectionOrFunction &&
            $this->arrXSort(
                $aTest,
                $this->AfrDirTraversingSortDirectionOrFunction,
                $bSortByKey,
                $this->AfrDirTraversingSortFlags,
            ) !== false
        ) {
            return $this->arrXSort(
                $aTest,
                $this->AfrDirTraversingSortDirectionOrFunction,
                $bSortByKey,
                $this->AfrDirTraversingSortFlags,
            );

        }
        if (count($arrayToSort) > 3) {
            $arrayToSort = array_slice($arrayToSort, 3);
            $arrayToSort[] = '...';
        }
        throw new FileSystemTraversingException(
            'Unable to call sort method in: ' . __FUNCTION__ . ' for ' .
            print_r([
                self::$GlobalAfrDirTraversingSortDirectionOrFunction,
                $this->AfrDirTraversingSortDirectionOrFunction, $bSortByKey,
                $this->AfrDirTraversingSortFlags,
                $arrayToSort
            ], true
            ),
        );

    }

}
