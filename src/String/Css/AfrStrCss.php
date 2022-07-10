<?php


namespace Autoframe\Core\String\Css;


use Autoframe\Core\String\Url\AfrStrUrl;


class AfrStrCss
{
    public static function base64EncodeImage(string $sFullImagePath): string
    {
        return AfrStrUrl::base64EncodeFile($sFullImagePath,'image');
    }
}