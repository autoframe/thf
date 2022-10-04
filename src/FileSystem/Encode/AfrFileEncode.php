<?php

namespace Autoframe\Core\FileSystem\Encode;

use Autoframe\Core\FileSystem\Encode\Exception\FileSystemEncodeException;

trait AfrFileEncode
{
    /**
     * @param string $sFullImagePath
     * @param string $fileType
     * @return string
     * @throws FileSystemEncodeException
     * CSS: .logo {background: url("<?php echo base64_encode_image ('img/logo.png','png'); ?>") no-repeat; }
     * <img src="<?php echo base64EncodeFile ('img/logo.png','image'); ?>"/>
     */
    public function base64EncodeFile(string $sFullImagePath, string $fileType = 'image'): string
    {
        //TODO: de adaugat aici verificare mime dupa extensie

        $filetype = pathinfo($sFullImagePath)['extension'];
        $binary = file_get_contents($sFullImagePath);
        if (!$binary || !$filetype) {
            throw new FileSystemEncodeException('Blank '.$fileType.' for base64 embed: ' . $sFullImagePath);
        }
        return 'data:' . $fileType . '/' . $filetype . ';base64,' . base64_encode($binary);
    }

}