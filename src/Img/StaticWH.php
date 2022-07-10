<?php


namespace Autoframe\Core\Img;

use Autoframe\Core\Exception\Exception;

trait StaticWH
{
    protected static int $iImgWidth;
    protected static int $iImgHeight;

    static function setImgWidth(int $iImgWidth)
    {
        self::$iImgWidth = $iImgWidth;
    }

    static function getImgWidth(): int
    {
        if (isset(self::$iImgWidth)) {
            return self::$iImgWidth;
        }
        throw new Exception('ImgWidth is not set');
    }

    static function imgWidth(int $iImgWidth = 0): int
    {
        if ($iImgWidth && $iImgWidth > 0) {
            self::$iImgWidth = $iImgWidth;
        }

        if (isset(self::$iImgWidth)) {
            return self::$iImgWidth;
        }
        return 0;
    }

    static function setImgHeight(int $iImgHeight)
    {
        self::$iImgHeight = $iImgHeight;
    }

    static function getImgHeight(): int
    {
        if (isset(self::$iImgHeight)) {
            return self::$iImgHeight;
        }
        throw new Exception('ImgHeight is not set');
    }

    static function imgHeight(int $iImgHeight = 0): int
    {
        if ($iImgHeight && $iImgHeight > 0) {
            self::$iImgHeight = $iImgHeight;
        }

        if (isset(self::$iImgHeight)) {
            return self::$iImgHeight;
        }
        return 0;
    }
}