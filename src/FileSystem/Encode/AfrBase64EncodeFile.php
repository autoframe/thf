<?php

namespace Autoframe\Core\FileSystem\Encode;

use Autoframe\Core\FileSystem\Encode\Exception\FileSystemEncodeException;
use Autoframe\Core\FileSystem\Mime\AfrFileMime;
//TODO php unit tests

trait AfrBase64EncodeFile
{
    use AfrFileMime;
    /**
     * @param string $sFullImagePath
     * @return string
     * @throws FileSystemEncodeException
     * CSS: .logo {background: url("<?php echo base64_encode_image ('img/logo.png','png'); ?>") no-repeat; }
     * <img src="<?php echo base64EncodeFile ('img/logo.png','image'); ?>"/>
     */
    public function base64EncodeFile(string $sFullImagePath): string
    {
        $sMime = $this->getMimeFromFileName($sFullImagePath);
        if (!$sMime) {
            throw new FileSystemEncodeException('Blank ' . $sMime . ' for base64 embed: ' . $sFullImagePath);
        }
        return 'data:' . $sMime. ';base64,' . file_get_contents($sFullImagePath);
    }

    /**
     * @return string
     */
    public function base64Encode1x1Gif(): string
    {
        return 'data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==';
    }

}