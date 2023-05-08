<?php


namespace Autoframe\Core\String\Css;

use Autoframe\Core\FileSystem\Encode\AfrBase64EncodeFileTrait;

trait AfrStrCss
{
    use AfrBase64EncodeFileTrait;
    public function base64EncodeImage(string $sFullImagePath): string
    {
        return $this->base64EncodeFile($sFullImagePath);
    }
}