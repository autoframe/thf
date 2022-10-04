<?php

namespace Autoframe\Core\Captcha\Classic;


use Autoframe\Core\Captcha\AfrCaptcha;
use Autoframe\Core\Exception\AutoframeException;
use Autoframe\Core\Image\AfrImageCaptchaTrait;
use Autoframe\Core\Object\AfrObjectSingletonTrait;
use Autoframe\Core\Session\AfrSessionFactory;
use Autoframe\Core\Session\AfrSessionPhp;


abstract class AfrCaptchaClassicImg extends AfrCaptcha
{
    use AfrObjectSingletonTrait;
    use AfrImageCaptchaTrait;

    const FONTFACTORSPLIT = '_ff';
    const FONTCACHEFILE = '_cache.json';
    const PWTOUPPPERCASE = true;
    const PNG_FORMAT = 1;
    const JPEG_FORMAT = 1;

    protected array $aParams = [
        'imgWidth' => 200 ,
        'imgHeight' => 80 ,
        'codeLength' => [3, 3, 'r'],
        'maxTextAngle' => 23,
        'fileTypeFormat' => self::JPEG_FORMAT
        //'setSecurityKey' => [       ['xxx', 0, 'first security key'],       ],
    ];

    public bool $bImproveSpeed = true;
    protected string $sSessionKey = 'security_code_' . __CLASS__;
    protected float $fFontFactor = 1;
    protected array $codeLength = [5, 5, 1]; // [$min, $max, $password_version];
    protected int $iMaxTextAngle = 17;
    protected int $iFormat = 2;

    protected string $sFontFile = '';
    private array $aFonts = [];
    protected string $sFontsDir = __DIR__ . DIRECTORY_SEPARATOR . 'Fonts' . DIRECTORY_SEPARATOR;

    function __construct(array $aParams = [])
    {
        parent::__construct($aParams);
        foreach ($this->getParams() as $functionName => $arg) {
            if (method_exists($this, $functionName)) {
                //call_user_func_array([$this, $functionName], $arg);
                $this->$functionName($arg);
            }
        }

    }

    private function fileTypeFormat(int $iFormat = 0): int
    {
        if($iFormat < 1){ //getter
            return $this->iFormat; //PNG defalut
        }
        if(
            $iFormat === self::PNG_FORMAT ||
            $iFormat === self::JPEG_FORMAT
        ){
            $this->iFormat = $iFormat;
        }
        return $this->iFormat;
    }

    private function codeLength(array $aSet = []): array
    {
        if ($aSet) {
            $min = $max = 5;
            $password_version = 1;
            if (count($aSet) === 1) {
                $min = (int)$aSet[0];
                if ($min < 4) {
                    $min = 4;
                }
                $max = $min;
            }
            if (count($aSet) > 1) {
                $min = min((int)$aSet[0], (int)$aSet[1]);
                $max = max((int)$aSet[0], (int)$aSet[1]);
                if ($min < 3) {
                    $min = 3;
                }
                if ($min < 3 && $this->imgVersion() === 3) {
                    $min = 3;
                }
                if ($max < 3) {
                    $max = 3;
                }
                if ($min > $max) {
                    $max = $min;
                }
            }
            if (isset($aSet[2])) {
                if (in_array((int)$aSet[2], [1, 2])) {
                    $password_version = (int)$aSet[2];
                } else {
                    $password_version = rand(1, 2);
                }
            }
            $this->codeLength = [$min, $max, $password_version];
        }
        return $this->codeLength;
    }


    protected int $ImgVersion;

    function imgVersion(): int
    {
        if (empty($this->ImgVersion)) {
            throw new AutoframeException('Invalid image version configured in class ' . __CLASS__ . '!');
        }
        return $this->ImgVersion;
    }


    protected function getFonts(): array
    {
        $aFonts = $this->getFontsFromDir($this->sFontsDir);
        if (!$aFonts) {
            throw new AutoframeException('No font files found for Captcha render!');
        }
        return $aFonts;
    }

    protected function getFontsFromDir(string $sDirPath, array $aExtensions = ['.ttf']): array
    {
        $aFiles = [];
        if (!is_dir($sDirPath) || count($aExtensions) < 1) {
            return $aFiles;
        }

        if (!$this->bImproveSpeed || !is_file($sDirPath . self::FONTCACHEFILE)) {
            $rDir = opendir($sDirPath);  //TODO de facut cu AfrDirPath operatile de directory
            while ($sFileName = readdir($rDir)) {
                $tf = $sDirPath . $sFileName;
                if ($sFileName != '.' && $sFileName != '..' && is_file($tf) && is_readable($tf)) {
                    foreach ($aExtensions as $filter) {
                        if (substr(strtolower($sFileName), -strlen($filter)) == strtolower($filter)) {
                            $aFiles[] = $sFileName;
                            break;
                        }
                    }

                }
            }
            closedir($rDir);
            natsort($aFiles);
            file_put_contents($sDirPath . self::FONTCACHEFILE, json_encode($aFiles));
        } else {
            $aFiles = json_decode(file_get_contents($sDirPath . self::FONTCACHEFILE), true);
        }
        return $aFiles;
    }

    protected function setFont(int $iCodeLength, string $sFontFile = ''): string
    {
        $aFonts = $this->getFonts();
        $this->sFontFile = $this->sFontsDir . $aFonts[array_rand($aFonts)];
        if ($sFontFile) {
            foreach ($aFonts as $sFontName) {
                if (strpos($sFontName, $sFontFile) !== false) {
                    $this->sFontFile = $this->sFontsDir . $sFontName;
                    break;
                }
            }
        }

        if (strpos($this->sFontFile, self::FONTFACTORSPLIT) !== false) {
            $fFontFactorDetect = explode(self::FONTFACTORSPLIT, $this->sFontFile)[1];
            $fFontFactorDetectBuffer = '';
            $aTargetFloatDetect = range(0, 9) + [10 => '.'];
            for ($i = 0; $i < strlen($fFontFactorDetect); $i++) {
                $sChar = substr($fFontFactorDetect, $i, 1);
                if (in_array($sChar, $aTargetFloatDetect)) {
                    $fFontFactorDetectBuffer .= $sChar;
                } else {
                    break;
                }
            }
            $fFontFactorDetectBuffer = strlen($fFontFactorDetectBuffer) ? floatval($fFontFactorDetectBuffer) : 0;
            if ($fFontFactorDetectBuffer > 0 && $fFontFactorDetectBuffer <= 2) {
                $this->fFontFactor = $fFontFactorDetectBuffer;
            } elseif ($fFontFactorDetectBuffer > 0 && $fFontFactorDetectBuffer <= 200) {
                $this->fFontFactor = $fFontFactorDetectBuffer / 100;
            }
        }

        if ($iCodeLength > 5) {
            for ($i = 6; $i <= $iCodeLength; $i++) {
                $this->fFontFactor *= 0.95;
            }
        }
        return $this->sFontFile;
    }

    protected function prepareCode(): string
    {
        $sCode = $this->generateCode();
        $this->sessionCode($sCode);
        return $sCode;
    }

    protected function sessionStart(): bool
    {
        /** @var AfrSessionPhp $session */
        $session = AfrSessionFactory::getInstance();
        if (!$session->session_started()) {
            if (!$session->session_start()) {
                throw new AutoframeException('Session could not be started!');
            }
        }
        return true;
    }

    protected function sessionCode(string $sCode = ''): string
    {
        $this->sessionStart();
        if ($sCode) {
            $_SESSION[$this->sSessionKey] = $sCode;
        }
        return !empty($_SESSION[$this->sSessionKey]) ? $_SESSION[$this->sSessionKey] : '';
    }

    public function newCaptchaResource(bool $bExitAfterFlush = true): void
    {
        $sCode = $this->prepareCode();
        $this->setFont(strlen($sCode));
        $this->createImage($sCode);
        $this->flushImage($sCode);
        if ($bExitAfterFlush) {
            exit;
        }
    }

    abstract function createImage(string $sCode);

    private function flushImage(string $sCode)
    {
        if (headers_sent()) {
            throw new AutoframeException('Image print error because headers are already sent!');
        }
        header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
        header('Pragma: no-cache');
        header('Expires: Wed, 18 Nov 1981 09:12:00 GMT');

        if(0 && $_SERVER['REMOTE_ADDR'] === '127.0.0.1' ){
            header('sFontFile: ' . basename($this->sFontFile));
            header('sCode: ' . $sCode);
        }

        if (function_exists('imageantialias')) {
            imageantialias($this->image, true);
        }

        if ($this->fileTypeFormat() === self::JPEG_FORMAT) {
            $sFormat = 'jpeg';
        } else {
            $sFormat = 'png';
        }
        header('Content-Type: image/'.$sFormat);
        header('Content-Disposition: filename="captcha.'.$sFormat);
        ('image'.$sFormat)($this->image);
        imagedestroy($this->image);
    }

    public function generateCode(): string
    {
        list($minLen, $maxLen, $v) = $this->codeLength();
        $password = '';
        srand((double)microtime() * 1000000);
        $iLength = rand($minLen, $maxLen);
        if ($v === 2) {
            //            $possible = '23456789bcdfghkmnpqrsuvwxyz';
            $vowels = array('a', 'e', 'u'); //, 'i','o'
            $cons = array('b', 'c', 'd', 'g', 'h', 'k', 'm', 'n', 'p', 'r', 's', 'v', //'j','t',
                'w', 'tr', 'cr', 'br', 'fr', 'th', 'dr', 'ch', 'ph', 'wr', 'st', 'sp', 'sw', 'pr');
            // removed 'l', 'sl', 'cl'

            $num_vowels = count($vowels);
            $num_cons = count($cons);

            if (rand(0, 1)) {
                $first = $cons;
                $num_first = $num_cons;
                $second = $vowels;
                $num_second = $num_vowels;
            } else {
                $first = $vowels;
                $num_first = $num_vowels;
                $second = $cons;
                $num_second = $num_cons;
            }

            for ($i = 0; $i < $iLength; $i++) {
                $add = $first[rand(0, $num_first - 1)] . $second[rand(0, $num_second - 1)];
                $password .= $add;
                $i += (strlen($add) - 1);
            }

            $password = substr($password, 0, $maxLen);
        } else {
            $possible = '23456789bcdfghkmnpqrsuvwxyz';
            $i = 0;
            while ($i < $iLength) {
                $password .= substr($possible, mt_rand(0, strlen($possible) - 1), 1);
                $i++;
            }
        }
        if (self::PWTOUPPPERCASE) {
            $password = strtoupper($password);
        }
        return $password;
    }


    protected function getImageParameters(int $iCodeLength, float $fHeightFit = 0.79): array
    {
//        $fHeightFit = 0.79;
        $iWidth = $this->imgWidth();
        $iHeight = $this->imgHeight();

        $iFontSize = floor($iHeight * $fHeightFit * $this->fFontFactor); //font size will be 75% of the image height

        $iWidthFontFitFactor = round($iWidth * $fHeightFit / $iFontSize / 3 * 5);
        if ($iCodeLength > $iWidthFontFitFactor) {
            $this->fFontFactor *= $iWidthFontFitFactor / $iCodeLength / 0.8 / (max(abs($iCodeLength - 3), 4) / 4);
            $iFontSize = floor($iWidthFontFitFactor / $iCodeLength * $iFontSize);
        }
        //echo $iFontSize.' / '.$iHeight.' @ '.$iWidth.' : '.$iWidthFontFitFactor." ^ $iCodeLength * FF {$this->fFontFactor}" ; die;
        return [
            $iWidth,
            $iHeight,
            $iFontSize
        ];
    }

    protected function maxTextAngle(int $iMaxTextAngle = 0): int
    {
        if ($iMaxTextAngle && $iMaxTextAngle > 0) {
            $this->iMaxTextAngle = $iMaxTextAngle;
        }

        if (isset($this->iMaxTextAngle)) {
            return $this->iMaxTextAngle;
        }
        return 0;
    }

}



/*

if( $_SESSION['security_code'] == strtolower($_POST['security_code']) && !empty($_SESSION['security_code']) ){
	$_SESSION['security_code']='';
	echo 'ok';
	}
else{echo 'bad code!';}

*/