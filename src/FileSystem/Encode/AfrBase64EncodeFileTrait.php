<?php

namespace Autoframe\Core\FileSystem\Encode;

use Autoframe\Components\FileMime\AfrFileMimeTrait;
//TODO php unit tests

trait AfrBase64EncodeFileTrait
{
    use AfrFileMimeTrait;
    /**
     * @param string $sFullImagePath
     * @return string
     * CSS: .logo {background: url("<?php echo base64EncodeFile ('img/logo.png'); ?>") no-repeat; }
     * <img src="<?php echo base64EncodeFile ('img/logo.png','image'); ?>"/>
     */
    public function base64EncodeFile(string $sFullImagePath): string
    {
        $sMime = $this->getMimeFromFileName($sFullImagePath);
        return 'data:' . $sMime. ';base64,' . base64_encode(file_get_contents($sFullImagePath));
    }

    /**
     * @return string
     */
    public function base64OnePxGif(): string
    {
        return 'data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==';
    }

}