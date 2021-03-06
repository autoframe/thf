<?php


namespace Autoframe\Core\Image;


use Autoframe\Core\Exception\Exception;

trait AfrImageCaptchaTrait
{
    protected $image;
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


    protected function initImage(int $iWidth, int $iHeight, bool $bTrueColor = true)
    {

        $this->image = $bTrueColor ? imagecreatetruecolor($iWidth, $iHeight) : imagecreate($iWidth, $iHeight);
        if (!$this->image) {
            throw new Exception('Cannot initialize new GD image stream');
        }
    }

    public function generateRandRGB(float $min = 12, float $max = 52, $signed = false, array $aMergeWith = [], bool $sum = true): array
    {
        $aRGB = [];
        for ($i = 0; $i < 3; $i++) {
            $iRand = mt_rand((int)$min, (int)$max);
            if ($signed) {
                $iRand = $iRand * (mt_rand(0, 1) * 2 - 1);
            }
            $aRGB[$i] = $iRand;
        }
        if($aMergeWith){
            return $this->sumRGBArrays($aRGB, $aMergeWith, $sum);
        }
        return $this->normalizeRGBArray($aRGB);
    }

    public function sumRGBArrays(array $aRGB = [], array $aMergeWith = [], bool $sum = true): array
    {
        if (count($aRGB) === 3 && count($aMergeWith) === 3) {
            $aRGBMerge = [];
            for ($i = 0; $i < 3; $i++) {
                $aRGBMerge[$i] = $aRGB[$i] + ($sum ? $aMergeWith[$i] : -$aMergeWith[$i]);
            }
            return $this->normalizeRGBArray($aRGBMerge);
        }
        throw new Exception('Expected RGB Array input of 3 int elements ranged 0 to 255');
    }

    public function normalizeRGBArray(array $aRGB = []): array
    {
        if (count($aRGB) === 3) {
            for ($i = 0; $i < 3; $i++) {
                $operated = intval($aRGB[$i]);
                $operated = min(255, $operated);
                $operated = max(0, $operated);
                $aRGB[$i] = $operated;
            }
            return $aRGB;
        }
        throw new Exception('Expected RGB Array input of 3 int elements ranged 0 to 255');

    }

    protected function imagecolorallocateRandRGB(int $min = 63, int $max = 191)
    {
        $aRand = $this->generateRandRGB($min, $max, false);
        return $this->imagecolorallocateAData($aRand);
    }

    protected function imageFillColor($iColor, int $iX = 0, int $iY = 0): bool
    {
        return imagefill($this->image, $iX, $iY, $iColor);
    }

    /**
     * @param array $aData
     * @return false|int
     * @throws Exception
     */
    protected function imagecolorallocateAData(array $aData)
    {
        if (count($aData) !== 3) {
            throw new Exception('Expected 3 int RGB!');
        }
        return imagecolorallocate($this->image, $aData[0], $aData[1], $aData[2]);
    }

    /**
     * Draw lines through an image
     * @param resource
     * @param int $x1
     * @param int $y1
     * @param int $x2
     * @param int $y2
     * @param int $imageColorAllocate
     * @param float $thick
     * @return void
     */
    //#0 imagelinethick(Object(GdImage), 708, 430, 708, 250, 1, 14)
    public function imagelinethick($image, int $x1, int $y1, int $x2, int $y2, int $imageColorAllocate, float $thick = 1): void
    {
        $t = $thick / 2 - 0.5;
        if ($thick === 1) {
            imagesetthickness($image, 1);
            imageline($image, $x1, $y1, $x2, $y2, $imageColorAllocate);
        } elseif ($x1 == $x2 || $y1 == $y2) {
            imagefilledrectangle($image, round(min($x1, $x2) - $t), round(min($y1, $y2) - $t), round(max($x1, $x2) + $t), round(max($y1, $y2) + $t), $imageColorAllocate);
        } else {
            $divisor = $x2 - $x1;
            $k = ($y2 - $y1) / ($divisor ?: 1); //y = kx + q
            $a = $t / sqrt(1 + pow($k, 2));
            $points = array(round($x1 - (1 + $k) * $a), round($y1 + (1 - $k) * $a), round($x1 - (1 - $k) * $a), round($y1 - (1 + $k) * $a), round($x2 + (1 + $k) * $a), round($y2 - (1 - $k) * $a), round($x2 + (1 - $k) * $a), round($y2 + (1 + $k) * $a),);
            imagefilledpolygon($image, $points, 4, $imageColorAllocate);
            imagepolygon($image, $points, 4, $imageColorAllocate);
        }
    }

    /**
     * Random length and thickness lines
     * @param $image
     * @param int $imageColorAllocate
     * @param int $iWidth
     * @param int $iHeight
     * @param float|int $fFontFactor
     * @param float|int $factorLevel
     * @param float|int $fThicknessFactor
     */
    public function generateRandomDotLines($image, int $imageColorAllocate, int $iWidth, int $iHeight, float $fFontFactor = 1, float $factorLevel = 1, float $fThicknessFactor = 1)
    {
        $iSize = (int)(($iWidth + $iHeight) / 2);
        $fLinesNbWithSize = 1.5 / sqrt($iSize);
        for ($i = 0; $i < ($iSize * (0.1 + $fLinesNbWithSize) * $fFontFactor * $fFontFactor * $fFontFactor * $factorLevel); $i++) {
            $MinLineThickness = max(1, floor($iSize * $fThicknessFactor / 75));
            $iMaxLineThickness = max($MinLineThickness, floor($iSize * $fThicknessFactor / 40));
            $this->imagelinethick(
                $image,
                mt_rand(0, $iWidth),
                mt_rand(0, $iHeight),
                mt_rand(0, $iWidth),
                mt_rand(0, $iHeight),
                $imageColorAllocate,
                rand(rand(1, $MinLineThickness), $iMaxLineThickness)
            );
        }
    }

    /**
     * Random dots
     * @param $image
     * @param int $imageColorAllocate
     * @param int $iWidth
     * @param int $iHeight
     * @param float|int $factorLevel
     */
    public function generateRandomDots($image, int $imageColorAllocate, int $iWidth, int $iHeight, float $factorLevel = 1)
    {
        for ($i = 0; $i < ($iWidth * $iHeight) * (($iWidth + $iHeight) / $iHeight) / 15 * $factorLevel; $i++) {
            imagefilledellipse(
                $image,
                mt_rand(0, $iWidth),
                mt_rand(0, $iHeight),
                rand(1, ceil($iHeight * (0.01))),
                rand(1, ceil($iHeight * (0.01))),
                $imageColorAllocate
            );
        }
    }

    /**
     * @param string $color
     * @return array
     */
    public function hexColorToDec(string $color = '#000000'): array
    {
        if (strlen($color) != 7 || substr($color, 0, 1) != '#') {
            return [];
        }
        return [
            hexdec(substr($color, 1, 2)),
            hexdec(substr($color, 3, 2)),
            hexdec(substr($color, 5, 2))
        ];
    }

    /**
     * @param float $fPercent
     * @return int
     */
    protected function getIWidthTextStart(float $fPercent = 0.07): int
    {
        return floor(imagesx($this->image) * $fPercent);
    }

    protected function getILetterSpace(int $iCodeLength, $fPercent = 0.885): int
    {
        return floor(imagesx($this->image) * $fPercent / $iCodeLength);
    }

    protected function writeTextOnImage(int $x, int $y, string $sText, int $iFontSize, int $sTextColor)
    {
        //imagettftext ($image, $size, $angle, $x, $y, $color, $fontfile, $text)
        imagettftext(
            $this->image,
            $iFontSize,
            0,
            $x,
            $y,
            $sTextColor,
            $this->sFontFile,
            $sText
        );

    }

    protected function writeCaptchaTextOnImage(string $sCode, int $iFontSize, array $aTextColors)
    {
        $iCodeLength = strlen($sCode);
        $iHeight = imagesy($this->image);
        $iMaxTextAngle = $this->maxTextAngle();

        $iXStart = $this->getIWidthTextStart();
        $iXEnd = $this->getILetterSpace($iCodeLength);
        for ($i = 0; $i < $iCodeLength; $i++) {
            //imagettftext ($image, $size, $angle, $x, $y, $color, $fontfile, $text)
            imagettftext(
                $this->image,
                $iFontSize-ceil($iFontSize*0.1),
                rand(-$iMaxTextAngle, $iMaxTextAngle),
                $iXStart + $i * $iXEnd,
                rand(ceil($iHeight * 0.67), floor($iHeight * 0.87)),
                $aTextColors[$i],
                $this->sFontFile,
                $sCode[$i]
            );
        }
    }

    protected function writeFakeCaptchaTextOnImage(string $sCode, int $iFontSize, array $aTextColors)
    {
        $iCodeLength = strlen($sCode);
        if($iCodeLength<6){
            $sCode = substr($sCode.$sCode.$sCode,0,6);
            $iCodeLength = strlen($sCode);
        }
        $iWidth = imagesx($this->image);
        $iHeight = imagesy($this->image);
        $iMaxTextAngle = $this->maxTextAngle();

        $iXStart = floor(-$iWidth * 0.15);
        $iXEnd = floor($iWidth * 1.15 / $iCodeLength);
        for ($i = 0; $i < $iCodeLength; $i++) {
            //imagettftext ($image, $size, $angle, $x, $y, $color, $fontfile, $text)
            imagettftext(
                $this->image,
                ceil($iFontSize*rand(185,266)/100),
                rand(-$iMaxTextAngle, $iMaxTextAngle),
                $iXStart + $i * $iXEnd,
                rand(ceil($iHeight * 0.85), floor($iHeight*1.07)),
                $aTextColors[$i%count($aTextColors)],
                $this->sFontFile,
                $sCode[$i]
            );
        }
    }

    public function imageRandRectangle(
        $image,
        int $lineColor,
        float $fThicknessFrom = 0.03,
        float $fThicknessTo = 0.07,
        float $fRectangleOffsetDelta = 0.15
    ): bool
    {
        $iWidth = imagesx($image);
        $iHeight = imagesy($image);

        $iWHSizeAvg = (int)(($iWidth + $iHeight) / 2);
        $iThicknessFrom = round($iWHSizeAvg * $fThicknessFrom);
        $iThicknessTo = round($iWHSizeAvg * $fThicknessTo);
        $iRectangleOffsetDelta = round($iWHSizeAvg * $fRectangleOffsetDelta);

        imagesetthickness(
            $image,
            rand($iThicknessFrom, $iThicknessTo)
        );
        // imagerectangle ( resource $image , int $x1 , int $y1 , int $x2 , int $y2 , int $color )
        // x1 Upper left x coordinate. y1 Upper left y coordinate 0, 0 is the top left corner of the image. x2 Bottom right x coordinate. y2 Bottom right y coordinate.
        return imagerectangle(
            $image,
            rand(-$iRectangleOffsetDelta, $iWidth - $iRectangleOffsetDelta),
            rand(-$iRectangleOffsetDelta, $iRectangleOffsetDelta),
            rand(-$iRectangleOffsetDelta, $iWidth + $iRectangleOffsetDelta),
            rand($iHeight - $iRectangleOffsetDelta, $iHeight + $iRectangleOffsetDelta),
            $lineColor
        );
    }

    public function arcLines($image, int $lineColor, int $iWidth, int $iHeight, int $iFontSize, float $factorLevel = 1, int $iCoveragePercent = 80)
    {
        $iThickness = ceil($iFontSize / rand(15, 20) * $factorLevel);
        imagesetthickness($image, $iThickness);

        $iCoveragePercent = max(15, abs($iCoveragePercent));
        $iCoveragePercent = min(150, $iCoveragePercent);

        $iAspectBase = round(min($iWidth, $iHeight) * $iCoveragePercent / 100);
        $iPadding = round((min($iWidth, $iHeight) - $iAspectBase) / 2);

        $aRatios = [0.65, 0.725, 0.8];
        $iEllipseW = round(rand((int)($iAspectBase * $aRatios[rand(0, 2)]), $iAspectBase));
        $iEllipseH = round(rand((int)($iAspectBase * $aRatios[rand(0, 2)]), $iAspectBase));

        $iEllipseCenterX = rand(
            round($iPadding + $iEllipseW / 2),
            round($iWidth - $iPadding - $iEllipseW / 2)
        );

        $iEllipseCenterY = rand(
            round($iPadding + $iEllipseH / 2),
            round($iHeight - $iPadding - $iEllipseH / 2)
        );

        $iRandArcStart = rand(0, 100);
        if ($iRandArcStart < 20) {
            $iArcStart = rand(0, 359);
            $iArcEnd = $iArcStart + rand(65, 140);
        } elseif ($iRandArcStart % 2) {
            $iArcStart = rand(15, 65);
            $iArcEnd = $iArcStart + rand(75, 120);
        } else {
            $iArcStart = rand(195, 245);
            $iArcEnd = $iArcStart + rand(75, 120);
        }

        imagearc($image, $iEllipseCenterX, $iEllipseCenterY, $iEllipseW, $iEllipseH, $iArcStart, $iArcEnd, $lineColor);
    }

    /**
     * Draw lines on the image
     *
     * @access private
     *
     */
    public function drawLines($image, int $lineColor, int $iWidth, int $iHeight, string $sPattern = '|-\/', float $factorLevel = 1)
    {
        $iLineDistance = ceil(min($iWidth, $iHeight) / 10 * $factorLevel);
        $iThickness = ceil($iLineDistance * 0.1);
        imagesetthickness($image, $iThickness);


        if (strpos($sPattern, '|') !== false || strpos($sPattern, '+') !== false) {
            //vertical lines
            for ($x = $iLineDistance; $x < $iWidth; $x += $iLineDistance) {
                imageline($image, $x, 0, $x, $iHeight, $lineColor);
            }
        }
        if (strpos($sPattern, '-') !== false || strpos($sPattern, '+') !== false) {
            //horizontal lines
            for ($y = $iLineDistance; $y < $iHeight; $y += $iLineDistance) {
                imageline($image, 0, $y, $iWidth, $y, $lineColor);
            }
        }

        if (strpos($sPattern, '\\') !== false || strpos(strtolower($sPattern), 'x') !== false) {
            $start = -($iHeight - ($iHeight % $iLineDistance));
            for ($x = $start; $x < $iWidth; $x += $iLineDistance) {
                imageline($image, $x, 0, $x + $iHeight, $iHeight, $lineColor);
            }
        }

        if (strpos($sPattern, '/') !== false || strpos(strtolower($sPattern), 'x') !== false) {
            $start = ($iWidth + $iHeight) - (($iWidth + $iHeight) % $iLineDistance);
            for ($x = $start; $x > 0; $x -= $iLineDistance) {
                imageline($image, $x, 0, $x - $iHeight, $iHeight, $lineColor);
            }
        }
    }

}