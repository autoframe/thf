<?php

namespace Autoframe\Core\Http\Header;

trait AfrHttpFullLog
{
    use AfrHttpRequested;

    protected function logHttpRequested($dir = '.'): array
    {
        $out = $this->getHttpRequested();

        //if(!$dir){$dir=__DIR__;}
        if (!$dir) {
            $dir = '.';
        }
        file_put_contents($dir . '/' . date('Y-m-d_H-i-s') .
            '_' . $_SERVER['REQUEST_METHOD'] . '_' .
            s($_SERVER['REQUEST_URI']) .
            '.txt', print_r($out, true));

        return $out;
    }


}