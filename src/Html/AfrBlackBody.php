<?php


namespace Autoframe\Core\Html;


class AfrBlackBody
{
    function __construct()
    {

        ob_start(function ($buffer) {
            $sBufferStart = strtolower(substr($buffer,0,100));
            if(substr($sBufferStart,0,1) === '{'){
                return $buffer;
            }

            $aCheckWords = ['<!DOCTYPE html>', 'html>', 'HTML>','PNG','JPG','JPEG','GIF','TTF','JFIF'];
            foreach ($aCheckWords as $sWord) {
                if (strpos($sBufferStart, strtolower($sWord)) !== false) {
                    return $buffer;
                }
            }

            return '<!DOCTYPE html><html><head><style>html,body{color:#f6f6f6;background:#1d1d1d;}</style></head><body>
'. $buffer.'
</body></html>';
        });
    }
}
