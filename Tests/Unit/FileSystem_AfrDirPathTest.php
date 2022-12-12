<?php

//https://www.youtube.com/watch?v=9-X_b_fxmRM&ab_channel=ProgramWithGio
namespace Unit;

use Autoframe\Core\FileSystem\DirPath\AfrDirPath;
use PHPUnit\Framework\TestCase;

class FileSystem_AfrDirPathTest extends TestCase
{
    use AfrDirPath;

    function dirPathIsDirDataProvider(): array
    {
        echo __CLASS__ . '->' . __FUNCTION__ . PHP_EOL;
        return [
            ['./', false],
            ['../', false],
            ['../../', false],
            ['../../', false],
            ['../../', false],
            [dirname(__FILE__), false],
            [__FILE__, false],
            ['ZZY', false],
            [dirname(__FILE__) . DIRECTORY_SEPARATOR . __FUNCTION__ . '_testDir', true],
        ];
    }

    /**
     * @test
     * @dataProvider dirPathIsDirDataProvider
     */
    public function dirPathIsDirTest(string $sDirPath, bool $bCreate): void
    {
        $bMaked = false;
        $bExpected = is_dir($sDirPath);
        if (!$bExpected && $bCreate) {
            $bMaked = mkdir($sDirPath, 0777, true);
            $bExpected = is_dir($sDirPath);
        }
        $r = $this->dirPathIsDir($sDirPath);
        $this->assertSame($r, $bExpected, print_r(func_get_args(), true));
        if ($bMaked) {
            rmdir($sDirPath);
        }
    }


    function getDirPathDefaultSeparatorsDataProvider(): array
    {
        echo __CLASS__ . '->' . __FUNCTION__ . PHP_EOL;
        return [
            [DIRECTORY_SEPARATOR],
            ['/'],
            ['\\'],
        ];
    }

    /**
     * @test
     * @dataProvider getDirPathDefaultSeparatorsDataProvider
     */
    public function getDirPathDefaultSeparatorsTest(string $sTestDs): void
    {
        $sFound = 'Directory separator is not Found';
        $aFrameworkDs = $this->getDirPathDefaultSeparators();
        foreach ($aFrameworkDs as $sSystemDs) {
            if ($sSystemDs === $sTestDs) {
                $sFound = $sSystemDs;
                break;
            }
        }
        $this->assertSame($sTestDs, $sFound, print_r($aFrameworkDs + func_get_args(), true));
    }


    function getDirPathIsDirAliasDataProvider(): array
    {
        echo __CLASS__ . '->' . __FUNCTION__ . PHP_EOL;
        return [
            ['.'],
            ['..'],
            ['a'],
            ['./'],
            ['../'],
            [chr(46)],
            [chr(46) . chr(46)],
            ["3"],
        ];
    }

    /**
     * @test
     * @dataProvider getDirPathIsDirAliasDataProvider
     */
    public function getDirPathIsDirAliasTest(string $sTest): void
    {
        $bResult = $this->getDirPathIsDirAlias($sTest);
        $bExpected = $sTest === '.' || $sTest === '..';
        $this->assertSame($bResult, $bExpected, print_r(func_get_args(), true));
    }


    function dirPathDetectDirectorySeparatorDataProvider(): array
    {
        echo __CLASS__ . '->' . __FUNCTION__ . PHP_EOL;
        return [
            [DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR],
            ['/', '/'],
            ['../', '/'],
            ['./', '/'],
            ['..', DIRECTORY_SEPARATOR],
            ['.', DIRECTORY_SEPARATOR],
            ['', DIRECTORY_SEPARATOR],
            ['999', DIRECTORY_SEPARATOR],
            [__DIR__, DIRECTORY_SEPARATOR],
            ['./dsadas/gfdgd/ffff', '/'],
            ['./dsadas/gfdgd\\ffff', '/'],
            ['\\dsadas\\gfdgd\\ffff', '\\'],
            ['C:\\Windows/system', '/'],
            ['C:\\Windows\\system', '\\'],
        ];
    }

    /**
     * @test
     * @dataProvider dirPathDetectDirectorySeparatorDataProvider
     */
    public function dirPathDetectDirectorySeparatorTest(string $sTestPath, string $sExpected): void
    {
        $this->assertSame(
            $this->dirPathDetectDirectorySeparator($sTestPath),
            $sExpected,
            print_r(func_get_args(), true)
        );
    }

    function dirPathValidateDetectSlashStyleDataProvider(bool $bSupressInfo = false): array
    {
        echo $bSupressInfo ? '' : __CLASS__ . '->' . __FUNCTION__ . PHP_EOL;
        ob_start();
        $aData = $this->dirPathDetectDirectorySeparatorDataProvider();
        ob_end_clean();
        return $aData;
    }

    /**
     * @test
     * @dataProvider dirPathValidateDetectSlashStyleDataProvider
     */
    public function dirPathValidateDetectSlashStyleTest(string $sTestPath, string $sExpected): void
    {
        foreach ([$sExpected, ''] as $sDs) {
            $this->assertSame(
                $this->dirPathValidateDetectSlashStyle($sTestPath, $sDs),
                $sExpected,
                print_r(func_get_args() + [$sDs], true)
            );
        }
    }


    function dirPathCorrectSlashStyleDataProvider(bool $bHide = false): array
    {
        echo $bHide ? '' : __CLASS__ . '->' . __FUNCTION__ . PHP_EOL;
        return [
            [DIRECTORY_SEPARATOR],
            ['/'],
            ['\\'],
            ['../'],
            ['./'],
            ['..'],
            ['.'],
            [''],
            ['999'],
            [__DIR__],
            [__DIR__ . DIRECTORY_SEPARATOR],
            ['./dsadas/gfdgd/ffff'],
            ['\dsadas/gfdgd/ffff'],
            ['./dsadas/gfdgd\\ffff'],
            ['\\dsadas\\gfdgd\\ffff'],
            ['\\\\dsadas\\gfdgd\\ffff\\'],
            ['C:\\Windows/system'],
            ['C:\\Windows\\system'],
        ];
    }

    /**
     * @test
     * @dataProvider dirPathCorrectSlashStyleDataProvider
     */
    public function dirPathCorrectSlashStyleTest(string $sTestPath): void
    {
        $aDs = $this->getDirPathDefaultSeparators();
        $aDs[] = '';
        foreach ($aDs as $sDs) {
            $sTestPathNew = $this->dirPathCorrectSlashStyle($sTestPath, $sDs);
            $aErr = [
                '$sTestPath' => $sTestPath,
                '$sDs' => $sDs,
                '$sTestPathNew' => $sTestPathNew,
            ];

            $aBefore = $this->count_slahes_private_tttst($sTestPath);
            $aAfter = $this->count_slahes_private_tttst($sTestPathNew);
            $iTotalBefore = array_sum($aBefore);
            $iTotalAfter = array_sum($aAfter);

            $aErr['$aBefore'] = $aBefore;
            $aErr['$iTotalBefore'] = $iTotalBefore;
            $aErr['$aAfter'] = $aAfter;
            $aErr['$iTotalAfter'] = $iTotalAfter;


            $this->assertSame(
                $iTotalBefore,
                $iTotalAfter,
                print_r($aErr, true)
            );
            if ($sDs) {
                $this->assertSame(
                    $iTotalBefore,
                    $aAfter[$sDs],
                    print_r($aErr, true)
                );
            } else {
                $this->assertSame(
                    $iTotalBefore,
                    $iTotalAfter,
                    print_r($aErr, true)
                );
            }

            if ($iTotalBefore > 0) { //shashes are found
                $iCountFoundInAfter = 0;
                foreach ($aAfter as $iCountDs) {
                    if ($iCountDs > 0) {
                        if ($iCountFoundInAfter) {
                            $this->assertSame(
                                $iTotalBefore,
                                $iCountDs,
                                print_r($aErr, true)
                            );
                        }
                        $iCountFoundInAfter += $iCountDs; //found only once after detection
                    }
                }
            }
        }
    }

    private function count_slahes_private_tttst(string $sTestPath): array
    {
        $aOut = [];
        foreach ($this->getDirPathDefaultSeparators() as $sDs) {
            $aOut[$sDs] = substr_count($sTestPath, $sDs);
        }
        return $aOut;
    }


    /**
     * @test
     * @dataProvider dirPathCorrectSlashStyleDataProvider
     */
    public function dirPathRemoveFinalSlashTest(string $sTestPath): void
    {
        foreach ($this->getDirPathDefaultSeparators() as $sDs) {
            $this->assertNotEquals(
                $sDs,
                substr($this->dirPathRemoveFinalSlash($sTestPath), -1, 1),
                'Fail: dirPathRemoveFinalSlash ' . $sTestPath
            );
        }
    }


    function dirPathAddFinalSlashDataProvider(): array
    {
        echo __CLASS__ . '->' . __FUNCTION__ . PHP_EOL;
        return [[]];
    }

    /**
     * @test
     * @dataProvider dirPathAddFinalSlashDataProvider
     */
    public function dirPathAddFinalSlashTest(): void
    {
        foreach ($this->dirPathValidateDetectSlashStyleDataProvider(true) as $aDatSet) {
            call_user_func_array([$this, 'dirPathValidateDetectSlashStyleTest'], $aDatSet);
        }
        $aDsDefault = $aDs = $this->getDirPathDefaultSeparators();
        $aDs[] = '';
        foreach ($this->dirPathCorrectSlashStyleDataProvider(true) as $sTestPath) {
            $sTestPath = $sTestPath[0];

            foreach ($aDs as $sDsParameter) {
                $sExpectedEndingSlash = $this->dirPathValidateDetectSlashStyle($sTestPath, $sDsParameter);
                $sTestPathReturn = $this->dirPathAddFinalSlash($sTestPath, $sDsParameter);
                $sLastChr = substr($sTestPathReturn, -1, 1);
                $sLastBut1Chr = strlen($sLastChr) > 1 ? substr($sTestPathReturn, -2, 1) : '';

                $this->assertEquals(
                    $sExpectedEndingSlash,
                    $sLastChr,
                    'Fail: dirPathAddFinalSlash ' . $sTestPath . ' : ' . $sTestPathReturn
                );
                if (strlen($sLastBut1Chr) > 0) {
                    foreach ($aDsDefault as $sBaseDs) {
                        $this->assertNotEquals(
                            $sBaseDs,
                            $sLastBut1Chr,
                            'Fail: dirPathAddFinalSlash ' . $sTestPath . ' : ' . $sTestPathReturn
                        );
                    }
                }
            }
        }
    }



    function openDirDataProvider(): array
    {
        echo __CLASS__ . '->' . __FUNCTION__ . PHP_EOL;
        return [
            ['.'],
            ['./'],
            ['../'],
            ['..'],
            ['../../'],
            ['../..'],
            [__DIR__],
            [dirname(__FILE__)],
        ];
    }

    /**
     * @test
     * @dataProvider openDirDataProvider
     */
    public function openDirTest(string $sDirPath): void
    {
        $rDir = $this->openDir($sDirPath);
        $bIsOpened = is_resource($rDir);
        $this->assertEquals(
            $bIsOpened,
            $this->dirPathIsDir($sDirPath),
            'Fail: openDir ' . $sDirPath
        );
        if($bIsOpened){
            closedir($rDir);
        }
       
    }




    function dirPathCorrectFormatDataProvider(): array
    {
        echo __CLASS__ . '->' . __FUNCTION__ . PHP_EOL;
        $set1 = './dsadas\\gfdgd/ffff';
        $set2 = 'C:\\Windows/system';
        $set3 = __DIR__;

        //$sExpected,  $sDirPathData,  $bWithFinalSlash = true, $bCorrectSlashStyle = true, &$sSlashStyle = DIRECTORY_SEPARATOR
        return [
            [DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, true, true, DIRECTORY_SEPARATOR ],
            [DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, true, true ],
            ['', DIRECTORY_SEPARATOR, false, true, DIRECTORY_SEPARATOR ],
            ['', DIRECTORY_SEPARATOR, false, true ],
            ['', DIRECTORY_SEPARATOR, false, false, DIRECTORY_SEPARATOR ],
            ['', DIRECTORY_SEPARATOR, false, false ],
            [DIRECTORY_SEPARATOR, '/', true, true, DIRECTORY_SEPARATOR ],
            [DIRECTORY_SEPARATOR, '/', true, true ],
            [DIRECTORY_SEPARATOR, '\\', true, true, DIRECTORY_SEPARATOR ],
            [DIRECTORY_SEPARATOR, '\\', true, true ],


            [str_replace('/', '\\', $set1) . '\\', $set1, true, true, '\\'],
            [str_replace('\\', '/', $set1) . '/', $set1, true, true, '/'],
            [str_replace('/', DIRECTORY_SEPARATOR, $set1) . DIRECTORY_SEPARATOR, $set1, true, true],
            [str_replace('/', DIRECTORY_SEPARATOR, $set1), $set1, false, true, DIRECTORY_SEPARATOR],
            [str_replace('/', DIRECTORY_SEPARATOR, $set1), $set1, false, true],
            [$set1, $set1 . '/', false, false, DIRECTORY_SEPARATOR],
            [$set1, $set1 . '/', false, false],

            [str_replace('/', '\\', $set2) . '\\', $set2, true, true, '\\'],
            [str_replace('\\', '/', $set2) . '/', $set2, true, true, '/'],
            [str_replace('/', DIRECTORY_SEPARATOR, $set2) . DIRECTORY_SEPARATOR, $set2, true, true],
            [str_replace('/', DIRECTORY_SEPARATOR, $set2), $set2, false, true, DIRECTORY_SEPARATOR],
            [str_replace('/', DIRECTORY_SEPARATOR, $set2), $set2, false, true],
            [$set2, $set2 . '/', false, false, DIRECTORY_SEPARATOR],
            [$set2, $set2 . '/', false, false],

            [str_replace('/', '\\', $set3) . '\\', $set3, true, true, '\\'],
            [str_replace('\\', '/', $set3) . '/', $set3, true, true, '/'],
            [str_replace('/', DIRECTORY_SEPARATOR, $set3) . DIRECTORY_SEPARATOR, $set3, true, true],
            [str_replace('/', DIRECTORY_SEPARATOR, $set3), $set3, false, true, DIRECTORY_SEPARATOR],
            [str_replace('/', DIRECTORY_SEPARATOR, $set3), $set3, false, true],
            [$set3, $set3 . '/', false, false, DIRECTORY_SEPARATOR],
            [$set3, $set3 . '/', false, false],
        ];
    }

    /**
     * @test
     * @dataProvider dirPathCorrectFormatDataProvider
     */
    public function dirPathCorrectFormatTest(
        string $sExpected,
        string $sDirPathData,
        bool   $bWithFinalSlash = true,
        bool   $bCorrectSlashStyle = true,
        string &$sSlashStyle = DIRECTORY_SEPARATOR
    ): void
    {
        $this->assertEquals(
            $this->dirPathCorrectFormat($sDirPathData, $bWithFinalSlash, $bCorrectSlashStyle, $sSlashStyle),
            $sExpected,
            'Fail: dirPathCorrectFormat ' . $sDirPathData . ' : ' . $sExpected."\nParams:". implode(';',func_get_args())
        );
    }


}