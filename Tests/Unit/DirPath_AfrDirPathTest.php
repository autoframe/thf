<?php

//https://www.youtube.com/watch?v=9-X_b_fxmRM&ab_channel=ProgramWithGio
namespace Unit;

use Autoframe\Core\FileSystem\DirPath\AfrDirPath;
use PHPUnit\Framework\TestCase;

class DirPath_AfrDirPathTest extends TestCase
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
        foreach ($aFrameworkDs as $sSystemDs){
            if($sSystemDs === $sTestDs){
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
            [chr(46).chr(46)],
            [(string)3],
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
    public function dirPathDetectDirectorySeparatorTest(string $sTest, string $sExpected): void
    {
        $this->assertSame(
            $this->dirPathDetectDirectorySeparator($sTest),
            $sExpected,
            print_r(func_get_args(), true)
        );
    }


    function dirPathValidateDetectSlashStyleDataProvider(): array
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
     * @dataProvider dirPathValidateDetectSlashStyleDataProvider
     */
    public function dirPathValidateDetectSlashStyleTest(string $sTest, string $sExpected): void
    {
        $this->assertSame(
            $this->dirPathDetectDirectorySeparator($sTest),
            $sExpected,
            print_r(func_get_args(), true)
        );
    }


}