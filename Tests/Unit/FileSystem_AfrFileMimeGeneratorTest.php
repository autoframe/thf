<?php

//https://www.youtube.com/watch?v=9-X_b_fxmRM&ab_channel=ProgramWithGio
namespace Unit;

use Autoframe\Core\FileSystem\Mime\Exception\AfrFileSystemMimeException;
use Autoframe\Core\FileSystem\Mime\AfrFileMimeGenerator;
use PHPUnit\Framework\TestCase;

class FileSystem_AfrFileMimeGeneratorTest extends TestCase
{
    use AfrFileMimeGenerator;

    function initFileMimeParseMimeTypesDataProvider(): array
    {
        echo __CLASS__ . '->' . __FUNCTION__ . PHP_EOL;
        $this->getUpdatedMimeTypesFromRepo();
        return [[]];
    }

    /**
     * @test
     * @dataProvider initFileMimeParseMimeTypesDataProvider
     */
    public function initFileMimeParseMimeTypesTest(): void
    {
        $iTsMimes = 0;
        $sErr = '';
        try {
            $iTsMimes = $this->initFileMimeParseMimeTypes();

        } catch (AfrFileSystemMimeException $e) {
            $sErr = $e->getMessage() . ";\n" . $e->getTraceAsString();
        }
        if (!$iTsMimes) {
            $this->assertSame(
                1,
                0,
                $sErr
            );
        } else {
            $this->assertGreaterThan(
                $iTsMimes-1,
                time(),
                'mime.types has a future timestamp, so the mime classes will be always regenerated!'
            );
        }
    }

}