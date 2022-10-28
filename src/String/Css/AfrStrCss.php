<?php


namespace Autoframe\Core\String\Css;

use Autoframe\Core\FileSystem\Encode\AfrBase64EncodeFile;

trait AfrStrCss
{
    use AfrBase64EncodeFile;
    public function base64EncodeImage(string $sFullImagePath): string
    {
        return $this->base64EncodeFile($sFullImagePath);
    }
}