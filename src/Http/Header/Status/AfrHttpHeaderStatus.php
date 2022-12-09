<?php

namespace Autoframe\Core\Http\Header\Status;


use Autoframe\Core\Http\Header\thfString;
use function Autoframe\Core\Http\Header\rpath;

trait AfrHttpHeaderStatus
{


    public function h301(string $loc)
    {
        http_response_code(301);
        header('Location: ' . $loc);
        die;
    }

    public function h302($loc = '')
    {
        header('HTTP/1.1 302 Found', true, 302);
        header('Location: ' . (($loc != '') ? $loc : $_SERVER['REQUEST_URI']));
        die;
    }

    //303redirect dupa ce postez ceva la un script pt rezultat....
    public function h303($loc = '')
    {
        header('HTTP/1.1 303 See Other', true, 303);
        header('Location: ' . (($loc != '') ? $loc : $_SERVER['REQUEST_URI']));
        die;
    }

    //307 miscat temporar ; intreb utilizatorul daca nu fac un request GET or HEAD; a nu se folosii forms
    public function h307($loc = '')
    {
        header("HTTP/1.1 307 Temporary redirect", true, 307);
        header('Location: ' . (($loc != '') ? $loc : $_SERVER['REQUEST_URI']));
        die;
    }

    public function h404($msg = '', $exit = true)
    {
        header('HTTP/1.1 404 Not Found', true, 404);
        echo $msg != '' ? $msg : '<h1>Page not found! 404</h1>' . $_SERVER['REQUEST_URI'];
        if (is_int($exit)) {
            die($exit);
        } elseif ($exit) {
            die();
        };
    }

    public function h405($html = 'HTTP/1.1 405 Method Not Allowed', $exit = true)
    {
        if (headers_sent() === false) {
            header("HTTP/1.1 405 Method Not Allowed", true, 405);
        }
        echo $html;
        if (is_int($exit)) {
            die($exit);
        } elseif ($exit) {
            die();
        }
    }

    public function h410($msg = '')
    {
        header('HTTP/1.1 410 Gone', true, 410);
        echo ($msg != '') ? $msg : '<h1>Page GONE! 410</h1>';
        die;
    }    //sters definitiv, remove from user cache


    public function e500($str = '')
    {
        @header($_SERVER["SERVER_PROTOCOL"] . ' 500 Internal Server Error', true, 500);
        @header('Status: 500 Internal Server Error');
        @header('Retry-After: 60');
        echo '<title>' . $_SERVER["SERVER_PROTOCOL"] . ' 500 Internal Server Error</title>
	<style>
	*{background:none !important;}
	</style>
	';
        if ($str == '') {
            $str = '<h1> HTTP/1.1 500 Internal Server Error</h1><h3>Sorry, something went wrong. Please contact your IT provider.</h3>';
        } else {
            if (substr_count($str, '<') < 1 && substr_count($str, '>') < 1) {
                $str = "<h1> $str </h1>";
            }

        }
        //echo 'THF backtrace: on Line:<strong>'.__LINE__.'</strong> in File:<strong>'.__FILE__.'</strong> and Func:<strong>'.__FUNCTION__ .'</strong><h3>Extended:</h3>';
        ob_start();
        debug_print_backtrace();
        $trace = ob_get_contents();
        ob_end_clean();

        $repalce = thfString::extract_between($trace, 'e500(', ") called at ")[0];
        $trace = str_replace($repalce, '', $trace);
        $trace = str_replace("\r", '', $trace);
        $trace = str_replace("\n", "\n\n\n", $trace);

        echo($str . '<br />T:' . date('Y-m-d H:i:s') . "<hr>\n"); //	prea(get_defined_vars());		prea(get_declared_classes());		prea(get_declared_interfaces());		prea(get_defined_functions());
        die(nl2br($trace));
    }


    public function h503($str = '')
    {
        $now = date('Y-m-d H:i:s');
        header('HTTP/1.1 503 Service Temporarily Unavailable', true, 503); //header('Status: 503 Service Temporarily Unavailable');
        header('Retry-After: 60');
        if ($str == '') {
            $str = "\r\n\r\n <h1>Lucrari de mentenanta...</h1><h3>Revenim in curand!</h3>" . $now;
        } else {
            $str = "\r\n\r\n<h1> $str </h1><br />" . $now;
        }
        echo $str;
        die;
    }

    public function pic_404($param = 0)
    {
        header("HTTP/1.1 404 Not Found");
        $filename = rpath() . 'upload/pics/0_404.jpg';
        if (!is_file($filename)) {
            die('404 NOT FOUND! NOT EVEN ERROR PICTURE IS PRESENT....');
        }
        $size = @filesize($filename);
        header('Content-Type: ' . get_mime_type($filename));
        header("Content-Length: " . $size);
        $handle = @fopen($filename, 'r');
        if ($handle) {
            while (($buffer = fgets($handle, 4096)) !== false) {
                ob_start();
                echo $buffer;
                ob_end_flush();
            }
            if (!feof($handle)) {
                echo "Error: unexpected file read fail\r\n";
            }
            fclose($handle);
        }
        die();
    }


}