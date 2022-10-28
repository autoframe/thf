<?php

//https://www.youtube.com/watch?v=9-X_b_fxmRM&ab_channel=ProgramWithGio
namespace Unit;

use Autoframe\Core\FileSystem\Mime\AfrFileMime;
use PHPUnit\Framework\TestCase;

class FileSystem_AfrFileMimeXTest extends TestCase
{
    use AfrFileMime;

    function getMimeFromFileNameDataProvider(): array
    {
        echo __CLASS__ . '->' . __FUNCTION__ . PHP_EOL;
        $sFallback = $this->getFileMimeFallback();
        $aOut = [
            ['./', $sFallback],
            ['../', $sFallback],
            ['../../', $sFallback],
            ['../../', $sFallback],
            ['../../', $sFallback],
            [dirname(__FILE__), $sFallback],
            [DIRECTORY_SEPARATOR, $sFallback],
            ['/', $sFallback],
            ['..', $sFallback],
            ['.', $sFallback],
            ['', $sFallback],
            ['999', $sFallback],
            [__FILE__, 'application/x-httpd-php'],
            ['test.doc', 'application/msword'],
            ['test.jpg', 'image/jpeg'],
            ['test.jpeg', 'image/jpeg'],
            ['test.gif', 'image/gif'],
            ['test.png', 'image/png'],
            ['test.svg', 'image/svg+xml'],
            ['test.mmr', 'image/vnd.fujixerox.edmics-mmr'],
        ];
        foreach (self::$aAfrFileMimeExtensions as $sExt => $sMime) {
            $aOut[] = ['test.' . ucwords($sExt), $sMime];
        }
        return $aOut;
    }

    /**
     * @test
     * @dataProvider getMimeFromFileNameDataProvider
     */
    public function getMimeFromFileNameTest(string $sPath, string $sMime): void
    {
        $this->assertSame(
            $sMime,
            $this->getMimeFromFileName($sPath),
            print_r(func_get_args(), true)
        );
    }


    function getExtensionsForMimeDataProvider(): array
    {
        echo __CLASS__ . '->' . __FUNCTION__ . PHP_EOL;
        return [
            ['image/jpeg', 'jpe|jpeg|jpg'],
        ];
    }

    /**
     * @test
     * @dataProvider getExtensionsForMimeDataProvider
     */
    public function getExtensionsForMimeTest(string $sMime,$sExpected): void
    {
        $a = $this->getExtensionsForMime($sMime);
        sort($a);
        $this->assertSame(
            implode('|',$a),
            $sExpected,
            print_r(func_get_args(), true)
        );
    }




    function getAllMimesFromFileNameDataProvider(): array
    {
        echo __CLASS__ . '->' . __FUNCTION__ . PHP_EOL;
        return [
            ['test.wmz', 'application/x-ms-wmz|application/x-msmetafile'],
        ];
    }

    /**
     * @test
     * @dataProvider getAllMimesFromFileNameDataProvider
     */
    public function getAllMimesFromFileNameTest(string $sExt, $sExpected): void
    {
        $a = $this->getAllMimesFromFileName($sExt);
        sort($a);
        $this->assertSame(
            implode('|',$a),
            $sExpected,
            print_r(func_get_args(), true)
        );
    }


}