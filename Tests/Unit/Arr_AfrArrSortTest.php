<?php

//https://www.youtube.com/watch?v=9-X_b_fxmRM&ab_channel=ProgramWithGio
namespace Unit;

use Autoframe\Core\Arr\AfrArrSort;
use PHPUnit\Framework\TestCase;

class Arr_AfrArrSortTest extends TestCase
{
    use AfrArrSort;

    function arrSortBySubKeyProvider(): array
    {
        $people = array(
            12345 => array(
                'id' => 12345,
                'first_name' => 'Joe',
                'surname' => 'Xloggs',
                'age' => 23,
                'sex' => 'm'
            ),
            12346 => array(
                'id' => 12346,
                'first_name' => 'Adam',
                'surname' => 'Smith',
                'age' => 18,
                'sex' => 'm'
            ),
            12347 => array(
                'id' => 12347,
                'first_name' => 'Amy',
                'surname' => 'Jones',
                'age' => 21,
                'sex' => 'f'
            )
        );
        $i = new \stdClass();
        $i->name = 'Ion';
        $b = new \stdClass();
        $b->name = 'Bella';
        $classes = ['i' => $i, 'b' => $b];

        echo __CLASS__ . '->' . __FUNCTION__ . PHP_EOL;
        return [
            [$people, 'age', SORT_DESC, [12345, 12347, 12346]],
            [$people, 'surname', SORT_ASC, [12347, 12346, 12345]],
            [$classes, 'name', SORT_ASC, ['b','i']],
        ];
    }

    /**
     * @test
     * @dataProvider arrSortBySubKeyProvider
     */
    public function arrSortBySubKeyTest(array $aToSort, string $sKey, int $iDirection, array $aExpectedIndexOrder): void
    {
        $aExpected = [];
        foreach($aExpectedIndexOrder as $mKey){
            $aExpected[$mKey] =  $aToSort[$mKey];
        }
        $aSorted = $this->arrSortBySubKey($aToSort,$sKey,$iDirection);
        $this->assertSame($aSorted, $aExpected, print_r(func_get_args(), true));
    }


    function ArrXSortDataProvider(): array
    {
        echo __CLASS__ . '->' . __FUNCTION__ . PHP_EOL;
        $aSet = $aTempSort = [
            'aa' => 'Â',
            'a' => 'r\\\'',
            'ca' => 'ă)',
            'A' => 0.,
            'n30' => '0.0',
            'n3' => 'Țg',
            'n31' => '!d',
            'cA' => -2.,
            85 => '&',
            'ș' => 'ț',
            'Ș' => '&',
            8 => '\\',
            71 => '%',
            71.2 => '-2',
            '70.2' => '-2.',
            ';' => -22,
            'â' => 'r',
            '#~' => 'R',
            "\tTAB" => 'a\"a',
            '#  ~' => 'Ă',
            7 => ".0",
        ];


        $out = [];
        foreach ([SORT_NATURAL, SORT_REGULAR] as $flags) {
            $aTempSort = $aSet;
            asort($aTempSort, $flags);
            $out['asort' . count($out)] = [$aSet, SORT_ASC, false, $flags, $aTempSort, 'asort'];

            $aTempSort = $aSet;
            arsort($aTempSort, $flags);
            $out['arsort' . count($out)] = [$aSet, SORT_DESC, false, $flags, $aTempSort, 'arsort'];

            $aTempSort = $aSet;
            ksort($aTempSort, $flags);
            $out['ksort' . count($out)] = [$aSet, SORT_ASC, true, $flags, $aTempSort, 'ksort'];

            $aTempSort = $aSet;
            krsort($aTempSort, $flags);
            $out['krsort' . count($out)] = [$aSet, SORT_DESC, true, $flags, $aTempSort, 'krsort'];
        }

        $fn = function ($a, $b) {
            return strnatcmp($a, $b);
        };

        $aTempSort = $aSet;
        uasort($aTempSort, $fn);
        $out['uasort' . count($out)] = [$aSet, $fn, false, 0, $aTempSort, 'uasort'];

        $aTempSort = $aSet;
        uksort($aTempSort, $fn);
        $out['uksort' . count($out)] = [$aSet, $fn, true, 0, $aTempSort, 'uksort'];

        $aTempSort = $aSet;
        natsort($aTempSort);
        $out['natsort' . count($out)] = [$aSet, 'natsort', null, 0, $aTempSort, 'natsort'];

        $aTempSort = $aSet;
        natcasesort($aTempSort);
        $out['natcasesort' . count($out)] = [$aSet, 'natcasesort', null, 0, $aTempSort, 'natcasesort'];

        return $out;
    }

    /**
     * @test
     * @dataProvider ArrXSortDataProvider
     */
    public function ArrXSortTest($aArray, $mDirectionOrCallableFn, $mSortByKey, $flags, $aExpectedSort, $fnTest): void
    {
        $this->arrXSort($aArray, $mDirectionOrCallableFn, $mSortByKey, $flags);
        $this->assertEquals(serialize($aArray), serialize($aExpectedSort), print_r(func_get_args(), true));
    }



}