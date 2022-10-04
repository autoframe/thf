<?php


namespace Autoframe\Core\String\Css;

use Autoframe\Core\FileSystem\Encode\AfrFileEncode;

trait AfrStrCss
{
    use AfrFileEncode;
    public function base64EncodeImage(string $sFullImagePath): string
    {
        return $this->base64EncodeFile($sFullImagePath);
    }
}