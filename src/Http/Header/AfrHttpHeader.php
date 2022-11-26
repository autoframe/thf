<?php

namespace Autoframe\Core\Http\Header;

use Autoframe\Core\Http\Header\Exception\AfrHttpHeaderException;

trait AfrHttpHeader
{

    public function redirect3xx(
        int    $iCode = 302,
        string $sLocation = '',
        bool   $bStripParams = false,
        array  $aBuildQuery = [],
        bool   $bExit = true
    ):void
    {
        if (!$sLocation) {
            $sLocation = $_SERVER['REQUEST_URI'];
        }
        if (count($_POST) && (!$iCode || $iCode == 302)) {
            $iCode = 303;
        }
        if ($bStripParams) {
            $sLocation = explode('?', $sLocation);
            $sLocation = $sLocation[0];
        }

        if (count($aBuildQuery)) {
            $loc_tmp = explode('?', $sLocation);
            if (isset($loc_tmp[1]) && strlen($loc_tmp[1])) {
                parse_str($loc_tmp[1], $aExisting);
            } else {
                $aExisting = array();
            }
            foreach ($aBuildQuery as $key => $val) {
                $aExisting[$key] = $val;
            }
            $sLocation = $loc_tmp[0] . '?' . http_build_query($aExisting);
        }
        $filename = $line = '';
        if (headers_sent() === false) {
            http_response_code($iCode);
            // 300 Multiple Choices
            // 301 Moved Permanently - Forget the old page existed. Convert to GET
            // 302 Found - Use the same method (GET/POST) to request the specified page
            // 303 See Other - Use GET to request the specified page. Use this to redirect after a POST.
            // 304 Not Modified use cache
            // 305 Use Proxy
            // 306 Switch Proxy
            // 307 Temporary Redirect - use for GET/HEAD; ELSE: ask user to redirect; do not use with forms!
            // 308 Permanent Redirect (experimental RFC7238)
            if ($iCode != 304) {
                header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
            }
            header('Location: '.urlencode($sLocation));

        } else {// Show the HTML?
            $sErrMsg = 'The automatic redirect failed because the output was started in '.$filename.' at line '.$line.';';
            error_log($sErrMsg.' The redirect target is: '.$sLocation,0);
            $sHLocation  = htmlentities($sLocation, ENT_QUOTES,'UTF-8');
            echo "$sErrMsg<br>\nHTTP #$iCode Location: <a href='$sHLocation'>" . $sHLocation . '</a>';
        }

        if($bExit){
            exit();
        }
    }


    public function json_encode_header($data, $die = 1)
    {
        header('Content-Type: application/json');
        echo json_encode($data);
        if ($die) {
            exit;
        }
    }



}