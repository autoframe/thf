<?php
declare(strict_types=1);

namespace Autoframe\Core\Http\Header\Status;
use Autoframe\Core\Http\Header\Exception\AfrHttpHeaderException;
use Autoframe\Core\Http\Request\AfrHttpRequest;



trait AfrHttpHeaderStatus
{
    use AfrHttpRequest;

    /**
     * 300 Multiple Choices;
     * 301 Moved Permanently - Forget the old page existed. Convert to GET;
     * 302 Found - Use the same method (GET/POST) to request the specified page;
     * 303 See Other - Use GET to request the specified page. Use this to redirect after a POST;
     * 304 Not Modified use cache;
     * 305 Use Proxy;
     * 306 Switch Proxy;
     * 307 Temporary Redirect - use for GET/HEAD; ELSE: ask user to redirect; do not use with forms!;
     * 308 Permanent Redirect (experimental RFC7238);
     * @param int $iCode
     * @param string $sLocation
     * @param bool $bStripGetParams
     * @param array $aBuildQuery
     * @param bool $bExit
     * @return void
     */
    public function hRedirect3xx(
        int    $iCode = 302,
        string $sLocation = '',
        bool   $bStripGetParams = false,
        array  $aBuildQuery = [],
        bool   $bExit = true
    ): void
    {
        $this->hXxxLog();
        if (!$sLocation) {
            if($iCode === 301){
                $iCode = 307; //prevent wrong permanent redirect
            }
            $sLocation = $_SERVER['REQUEST_URI'];
        }
        if (count($_POST) && (!$iCode || $iCode == 302)) {
            $iCode = 303;
        }
        if ($bStripGetParams) {
            $sLocation = explode('?', $sLocation);
            $sLocation = $sLocation[0];
        }

        if (count($aBuildQuery)) {
            $sLoc_tmp = explode('?', $sLocation);
            $aExisting = [];
            if (isset($sLoc_tmp[1]) && strlen($sLoc_tmp[1])) {
                parse_str($sLoc_tmp[1], $aExisting);
            }
            foreach ($aBuildQuery as $key => $val) {
                $aExisting[$key] = $val;
            }
            $sLocation = $sLoc_tmp[0] . '?' . http_build_query($aExisting);
        }
        $filename = $line = '';
        if (headers_sent() === false) {
            http_response_code($iCode);
            if ($iCode != 304) {
                header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
            }
            header('Location: ' . urlencode($sLocation));

        } else {// Show the HTML?
            $sErrMsg = 'The automatic redirect failed because the output was started in ' . $filename . ' at line ' . $line . ';';
            error_log($sErrMsg . ' The redirect target is: ' . $sLocation, 0);
            $sHLocation = htmlentities($sLocation, ENT_QUOTES, 'UTF-8');
            echo "$sErrMsg<br>\nHTTP #$iCode Location: <a href='$sHLocation'>" . $sHLocation . '</a>';
        }

        if ($bExit) {
            exit;
        }
    }

    /** Permanent redirect
     * @param string $sLoc
     * @param bool $bExit
     * @return void
     * @throws AfrHttpHeaderException
     */
    public function h301(string $sLoc, bool $bExit = true): void
    {
        $this->hXxxLog();
        if($sLoc === $_SERVER['REQUEST_URI']){
            throw new AfrHttpHeaderException('Error making a permanent redirect to the same loop page: '.$sLoc);
        }
        http_response_code(301);
        header('Location: ' . $sLoc);
        if ($bExit) {
            exit;
        }
    }

    /**
     * @param string $sLoc
     * @param bool $bExit
     * @return void
     */
    public function h302(string $sLoc, bool $bExit = true): void
    {
        $this->hXxxLog();
        http_response_code(302);
        header('Location: ' . (($sLoc != '') ? $sLoc : $_SERVER['REQUEST_URI']));
        if ($bExit) {
            exit;
        }
    }

    /** Use this with forms when making browser POST requests.
     * @param string $sLoc
     * @param bool $bExit
     * @return void
     */
    public function h303(string $sLoc, bool $bExit = true): void
    {
        $this->hXxxLog();
        http_response_code(303);
        header('Location: ' . (($sLoc != '') ? $sLoc : $_SERVER['REQUEST_URI']));
        if ($bExit) {
            exit;
        }
    }


    /** Temporary redirect; No not use with forms!
     *  Ask the user if is not a GTE or HEAD request
     * @param string $sLoc
     * @param bool $bExit
     * @return void
     */
    public function h307(string $sLoc, bool $bExit = true): void
    {
        $this->hXxxLog();
        http_response_code(307);
        header('Location: ' . (($sLoc != '') ? $sLoc : $_SERVER['REQUEST_URI']));
        $this->hXxxLog();

        if ($bExit) {
            exit;
        }
    }

    /**
     * @param string $sMsg
     * @param bool $bExit
     * @return void
     */
    public function h404(string $sMsg = '', bool $bExit = true): void
    {
        $this->hXxxLog();
        header('HTTP/1.1 404 Not Found', true, 404);
        echo $sMsg != '' ? $sMsg : '<h1>Page not found! 404</h1>' . $_SERVER['REQUEST_URI'];
        if ($bExit) {
            exit;
        }
    }

    public function h405($html = 'HTTP/1.1 405 Method Not Allowed', $exit = true)
    {
        $this->hXxxLog();

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
        $this->hXxxLog();

        header('HTTP/1.1 410 Gone', true, 410);
        echo ($msg != '') ? $msg : '<h1>Page GONE! 410</h1>';
        die;
    }    //sters definitiv, remove from user cache


    public function e500($str = '')
    {
        $this->hXxxLog();

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
        $this->hXxxLog();

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
        $this->hXxxLog();

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