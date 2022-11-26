<?php

namespace Autoframe\Core\FileSystem\Encode;

use Autoframe\Core\FileSystem\Encode\Exception\AfrFileSystemEncodeException;

interface AfrBase64EncodeFileInterface
{
    /**
     * @param string $sFullImagePath
     * @return string
     * @throws AfrFileSystemEncodeException
     * CSS: .logo {background: url("<?php echo base64_encode_image ('img/logo.png','png'); ?>") no-repeat; }
     * <img src="<?php echo base64EncodeFile ('img/logo.png','image'); ?>"/>
     */
    public function base64EncodeFile(string $sFullImagePath): string;

    /**
     * @return string
     */
    public function base64Encode1x1Gif(): string;

}