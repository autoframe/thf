<?php


namespace Autoframe\Core\Img;

use Autoframe\Core\Exception\AfrException;

trait InstanceWH
{

    protected int $iImgWidth;
    protected int $iImgHeight;

    function imgHeight(int $iImgHeight = 0): int
    {
        if ($iImgHeight && $iImgHeight > 0) {
            $this->iImgHeight = $iImgHeight;
        }

        if (isset($this->iImgHeight)) {
            return $this->iImgHeight;
        }
        return 0;
    }

    function imgWidth(int $iImgWidth = 0): int
    {
        if ($iImgWidth && $iImgWidth > 0) {
            $this->iImgWidth = $iImgWidth;
        }

        if (isset($this->iImgWidth)) {
            return $this->iImgWidth;
        }
        return 0;
    }

    function setImgHeight(int $iImgHeight)
    {
        $this->iImgHeight = $iImgHeight;
    }

    function getImgHeight(): int
    {
        if (isset($this->iImgHeight)) {
            return $this->iImgHeight;
        }
        throw new AfrException('ImgHeight is not set');
    }

    function setImgWidth(int $iImgWidth)
    {
        $this->iImgWidth = $iImgWidth;
    }

    function getImgWidth(): int
    {
        if (isset($this->iImgWidth)) {
            return $this->iImgWidth;
        }
        throw new AfrException('ImgWidth is not set');
    }

}