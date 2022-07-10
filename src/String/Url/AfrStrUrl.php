<?php


namespace Autoframe\Core\String\Url;

use Autoframe\Core\Exception\Exception;
use function parse_url;
use function parse_str;
use function rtrim;
use function strtr;
use function base64_encode;
use function base64_decode;
use function str_pad;
use function strlen;
use function pathinfo;
use function file_get_contents;

class AfrStrUrl
{
    /**
     * @param string $url 'https://www.youtube.com/watch?v=q1uVg13zDwM&gg=1'
     * @return array
     * reverse:  http_build_query($array);
     */
    public static function parseUrlGetParams(string $url): array
    {
        $output = [];
        $url = parse_url($url);
        if ($url['query']) {
            parse_str($url['query'], $output);
        }
        return $output;
    }

    /**
     * @param $data
     * @return string
     */
    public static function base64url_encode($data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * @param $data
     * @return false|string
     */
    public static function base64url_decode(string $data)
    {
        return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
    }

    /**
     * @param string $sFullImagePath
     * @param string $fileType
     * @return string
     * @throws Exception
     * CSS: .logo {background: url("<?php echo base64_encode_image ('img/logo.png','png'); ?>") no-repeat; }
     * <img src="<?php echo base64EncodeFile ('img/logo.png','image'); ?>"/>
     */
    public static function base64EncodeFile(string $sFullImagePath, string $fileType = 'image'): string
    {
        $filetype = pathinfo($sFullImagePath)['extension'];
        $binary = file_get_contents($sFullImagePath);
        if (!$binary) {
            throw new Exception('Blank '.$fileType.' for base64 embed: ' . $sFullImagePath);
        }
        return 'data:' . $fileType . '/' . $filetype . ';base64,' . base64_encode($binary);
    }

}